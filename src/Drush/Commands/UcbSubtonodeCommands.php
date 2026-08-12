<?php

namespace Drupal\ucb_subtonode\Drush\Commands;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for ucb_subtonode.
 */
final class UcbSubtonodeCommands extends DrushCommands {

  /**
   * Grants bulletin permissions to developer, architect, site_manager, and content_editor.
   */
  #[CLI\Command(name: 'ucb_subtonode:grant-permissions', aliases: ['ucbstngp'])]
  #[CLI\Usage(name: 'ucb_subtonode:grant-permissions', description: 'Grants bulletin and create-from-submission permissions to editorial roles')]
  public function grantPermissions(): void {
    \Drupal::moduleHandler()->loadInclude('ucb_subtonode', 'install');

    $results = _ucb_subtonode_grant_permissions();

    foreach ($results as $role_id => $status) {
      switch ($status) {
        case 'updated':
          $this->logger()->notice(dt('Updated permissions for @role role.', [
            '@role' => $role_id,
          ]));
          break;

        case 'unchanged':
          $this->logger()->notice(dt('Permissions already set for @role role.', [
            '@role' => $role_id,
          ]));
          break;

        case 'missing':
          $this->logger()->error(dt('@role role does not exist.', [
            '@role' => $role_id,
          ]));
          break;
      }
    }

    $this->logger()->success(dt('Finished granting ucb_subtonode permissions.'));
  }

}
