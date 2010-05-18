<?php
App::import('Behavior', 'MeioDuplicate.MeioDuplicate');

// define the PATH of source folder of the image we are using for testing.
define('MEIODUPLICATE_SOURCE_TEST_IMAGES_FOLDER_PATH', dirname(__FILE__) . DS . '..' . DS . '..' . DS . 'img' . DS);

// define the FILENAME of the test image
define('MEIODUPLICATE_SOURCE_TEST_IMAGE', 'box.jpg');

// define the destination folder of the image we are using for testing.
define('DESTINATION_TEST_IMAGE_FOLDER_FOR_MEIODUPLICATE', 'meiomodel');

// for the dir option inside the MeioDuplicate. reads as uploads{DS}meioupload
define('MEIODUPLICATE_DIR_OPTION', 'uploads{DS}' . DESTINATION_TEST_IMAGE_FOLDER_FOR_MEIODUPLICATE);

class Meiomodel extends CakeTestModel {
	var $name = 'Meiomodel';
	var $useTable = false;
	var $actsAs = array(
		'MeioDuplicate.MeioDuplicate'=> array(
			'filename' => array(
				'thumbsizes' => array(
					'small'  => array('width'=>60, 'height'=>60),
					'large'  => array('width'=>800, 'height'=>400),
					'shop'  => array('width'=>265, 'height'=>265)
				),
				'dir' => MEIODUPLICATE_DIR_OPTION,
			)
		),
	);
}

class MeioDuplicateTestCase extends CakeTestCase {

	var $MeioDuplicate = null;
	var $TestModel = null;

	function start() {
		parent::start();
		$this->TestModel = new Meiomodel();
		$this->MeioDuplicate =& $this->TestModel->Behaviors->MeioDuplicate;


	}

	function end() {
		// if you comment these 2 lines you will be able to see the test images successfully duplicated over
		// to the folder webroot/uploads/meiomodel
		$folder =& new Folder(WWW_ROOT . 'uploads' . DS . DESTINATION_TEST_IMAGE_FOLDER_FOR_MEIODUPLICATE);
		$folder->delete();

		parent::end();
	}

	function testDuplicateImage() {

		// test for boolean false result for non-existent source file
		$result = $this->TestModel->duplicateImage(MEIODUPLICATE_SOURCE_TEST_IMAGES_FOLDER_PATH . 'nosuchfile.jpg');
		$this->assertEqual($result, false);

		// test for boolean true result for existent source file
		$result = $this->TestModel->duplicateImage(MEIODUPLICATE_SOURCE_TEST_IMAGES_FOLDER_PATH . MEIODUPLICATE_SOURCE_TEST_IMAGE);
		$this->assertEqual($result, true);

		// test if the duplicate was really created in the destination folder
		$result = file_exists(WWW_ROOT . 'uploads' . DS . DESTINATION_TEST_IMAGE_FOLDER_FOR_MEIODUPLICATE . DS . MEIODUPLICATE_SOURCE_TEST_IMAGE);
		$this->assertEqual($result, true);

		// test if the thumb was really created in the respective destination folder
		$fullDestinationPath = WWW_ROOT . 'uploads' . DS . DESTINATION_TEST_IMAGE_FOLDER_FOR_MEIODUPLICATE . DS;
		$result = file_exists($fullDestinationPath . 'thumb' . DS . 'small' . DS . MEIODUPLICATE_SOURCE_TEST_IMAGE);
		$result = $result && file_exists($fullDestinationPath . 'thumb' . DS . 'large' . DS . MEIODUPLICATE_SOURCE_TEST_IMAGE);
		$result = $result && file_exists($fullDestinationPath . 'thumb' . DS . 'shop' . DS . MEIODUPLICATE_SOURCE_TEST_IMAGE);
		$this->assertEqual($result, true);

		// test an actual model with a datatable tied to it.

		/**
		 * uncomment this if you want to test with a model that uses a datatable.
		 *
		// please replace this with the Model name that is attached to MeioDuplicate and uses a datatable
		$image =  ClassRegistry::init('YOUR_MODEL_NAME');

		$image->duplicateImage(MEIODUPLICATE_SOURCE_TEST_IMAGES_FOLDER_PATH . MEIODUPLICATE_SOURCE_TEST_IMAGE);

		$destinationFolderInWebroot = $image->actsAs['MeioDuplicate.MeioDuplicate']['filename']['dir'];

		$order   = array("{DS}");
		$replace = DS;

		// Replace {DS} with the proper DS in your cake.
		$destinationFolderInWebroot = str_replace($order, $replace, $destinationFolderInWebroot);

		$expected = array('ProductImage' => array(
							      'dir' => $destinationFolderInWebroot,
							      'mimetype' => 'image/jpeg',
							      'filesize' => 8667,
							      'filename' => MEIODUPLICATE_SOURCE_TEST_IMAGE,
							      )
				);


		$this->assertEqual($image->data, $expected);

		// need to remove the image that was copied
		$file =& new File(WWW_ROOT . $destinationFolderInWebroot . DS . MEIODUPLICATE_SOURCE_TEST_IMAGE);
		$file->delete();
		**/



	}


}
?>