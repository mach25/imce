<?php

namespace Drupal\Tests\imce\Kernel\Plugin\ImcePlugin;

use Drupal\imce\ImceFolder;
use Drupal\imce\Plugin\ImcePlugin\Delete;
use Drupal\Tests\imce\Kernel\Plugin\KernelTestBasePlugin;

/**
 * Kernel tests for Imce plugins for Imce Plugin Core.
 *
 * @group imce
 */
class DeleteTest extends KernelTestBasePlugin {

  /**
   * The Imce ckeditor plugin.
   *
   * @var \Drupal\imce\Plugin\ImcePlugin\Delete
   */
  public $delete;

  /**
   * The Imce file manager.
   *
   * @var \Drupal\imce\ImceFM
   */
  public $imceFM;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'user',
    'system',
    'imce',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->delete = new Delete([], "text_textarea_with_summary", $this->getPluginDefinations());
    $this->imceFM = $this->getImceFM();
  }

  /**
   * Set the ImceFM::selection[].
   */
  public function setSelection() {
    $this->imceFM->selection[] = $this->imceFM->createItem(
      'file', "ciandt.jpg", ['path' => '.']
    );
    $this->imceFM->selection[0]->parent = new ImceFolder('.', $this->getConf());
    $this->imceFM->selection[0]->parent->setFm($this->imceFM);
    $this->imceFM->selection[0]->parent->setPath('.');
  }

  /**
   * Set the active folder.
   */
  public function setActiveFolder() {
    $this->imceFM->activeFolder = new ImceFolder('.', $this->getConf());
    $this->imceFM->activeFolder->setPath('.');
    $this->imceFM->activeFolder->setFm($this->imceFM);
  }

  /**
   * Set the request parameters.
   */
  public function setParametersRequest() {
    $this->imceFM->request->request->add([
      'jsop' => 'delete',
      'token' => 'LLuA1R0aUOzoduSJkJxN5aoHVdJnQk8LbTBgdivOU4Y',
      'active_path' => '.',
      'selection' => ['folder-test-delete'],
    ]);
  }

  /**
   * Test Delete::permissionInfo()
   */
  public function testPermissiomInfo() {
    $permissionInfo = $this->delete->permissionInfo();
    $this->assertTrue(is_array($permissionInfo));
    $this->assertTrue(in_array('Delete files', $permissionInfo));
    $this->assertTrue(in_array('Delete subfolders', $permissionInfo));
  }

  /**
   * Teste messages on context ImcePlugin\Delete.
   */
  public function testMessages() {
    $messages = $this->imceFM->getMessages();
    $this->assertTrue(is_array($messages));
    $this->assertEquals([], $messages);
  }

}
