<?php

namespace Drupal\Tests\imce\Kernel\Plugin\ImcePlugin;

use Drupal\imce\ImceFM;
use Drupal\imce\ImceFolder;
use Drupal\imce\ImcePluginInterface;
use Drupal\imce\Plugin\ImcePlugin\Upload;
use Drupal\Tests\imce\Kernel\Plugin\KernelTestBasePlugin;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Kernel tests for Imce plugins for Imce Plugin Core.
 *
 * @group imce
 */
class UploadTest extends KernelTestBasePlugin {

  /**
   * The Imce ckeditor plugin.
   *
   * @var \Drupal\imce\Plugin\ImcePlugin\Upload
   */
  public $upload;

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
    $this->imceFM = $this->getImceFM();
    $this->upload = new Upload([], "upload", $this->getPluginDefinations());

    $this->setParametersRequest();
    $this->setActiveFolder();
  }

  /**
   * Get the request parameter.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   The request object.
   */
  public function getRequest() {
    $request = Request::create("/imce", 'POST', [
      'jsop' => 'upload',
      'token' => 'LLuA1R0aUOzoduSJkJxN5aoHVdJnQk8LbTBgdivOU4Y',
      'active_path' => '.',
      'files[imce][]' => 'file.txt',
    ]);
    $session = new Session();
    $session->set('imce_active_path', '.');
    $request->setSession($session);

    return $request;
  }

  /**
   * This method will be removed.
   */
  public function test() {
    $this->assertEquals('test', 'test');
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
      'jsop' => 'upload',
      'token' => 'LLuA1R0aUOzoduSJkJxN5aoHVdJnQk8LbTBgdivOU4Y',
      'active_path' => '.',
      'files[imce][]' => 'file.txt',
    ]);
  }

  /**
   * Test Upload::permissionInfo()
   */
  public function testPermissionInfo() {
    $permissionInfo = $this->upload->permissionInfo();
    $this->assertTrue(is_array($permissionInfo));
    $this->assertTrue(in_array('Upload files', $permissionInfo));
  }

  /**
   * Teste messages on context ImcePlugin\Upload.
   */
  public function testMessages() {
    $messages = $this->imceFM->getMessages();
    $this->assertTrue(is_array($messages));
    $this->assertEquals([], $messages);
  }

  /**
   * Test Upload type.
   */
  public function testCore() {
    $this->assertInstanceOf(ImcePluginInterface::class, $this->upload);
  }

  /**
   * Test upload operation.
   */
  public function testOperation() {
    $this->assertEquals($this->imceFM->getOp(), 'upload');
  }

}
