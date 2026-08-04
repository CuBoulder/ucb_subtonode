<?php

namespace Drupal\ucb_subtonode\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Creates bulletin nodes from webform submissions.
 */
class SubToNodeController extends ControllerBase {

  /**
   * Creates a bulletin node from a webform submission.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   The webform submission entity.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect to the newly created bulletin node.
   */
  public function subtonode(WebformSubmissionInterface $webform_submission): RedirectResponse {
    $submission_array = $webform_submission->getData();
    $webform_submission_id = $webform_submission->id();
    $title = $submission_array['title'] ?? '';

    $timestamp = NULL;
    if (!empty($submission_array['bulletin_publish_date'])) {
      $timestamp = date('Y-m-d\TH:i:s', strtotime($submission_array['bulletin_publish_date']));
    }

    $website = $submission_array['website'] ?? '';
    $media_id = ucb_subtonode_resolve_media_id($submission_array['image'] ?? NULL);

    $node = Node::create([
      'type' => 'bulletin',
      'title' => $title,
      'body' => [
        'value' => $submission_array['body'] ?? '',
        'summary' => '',
        'format' => 'wysiwyg',
      ],
      'field_bulletin_contact_name' => $submission_array['contact_name'] ?? '',
      'field_bulletin_contact_email' => $submission_array['contact_email'] ?? '',
      'field_bulletin_desired_publicati' => $timestamp,
      'field_bulletin_contact_website' => [
        'uri' => $website,
        'title' => $website,
      ],
      'field_photo' => [
        'target_id' => $media_id,
      ],
    ]);

    if (!empty($submission_array['audience'])) {
      foreach ($submission_array['audience'] as $target_id) {
        $node->get('field_bulletin_audience')->appendItem(['target_id' => $target_id]);
      }
    }

    if (!empty($submission_array['category'])) {
      foreach ($submission_array['category'] as $target_id) {
        $node->get('field_bulletin_category')->appendItem(['target_id' => $target_id]);
      }
    }

    $node->save();

    // Remove the source submission once it has been promoted to a node.
    $webform_submission->delete();

    $this->messenger()->addStatus($this->t('Created a bulletin node from webform submission @sid and deleted the submission.', [
      '@sid' => $webform_submission_id,
    ]));

    return $this->redirect('entity.node.canonical', ['node' => $node->id()]);
  }

}
