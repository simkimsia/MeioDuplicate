# MeioDuplicate 1.0 Behavior Plugin

This behavior extends the current MeioDuplicate 2.0 plugin by jrbasso at http://github.com/jrbasso/MeioUpload

Basically this behavior allows you to duplicate any image already existing in your cake app.


## Installation
- Clone from github: in your behaviors directory type `git clone git://github.com/keisimone/MeioDuplicate.git plugins/meio_duplicate`
- Add as a git submodule: in your behaviors directory type `git submodule add git://github.com/keisimone/MeioDuplicate.git plugins/meio_duplicate`
- Download an archive from github and extract it in `plugins/meio_duplicate`

* If you require thumbnails for image generation, download the latest copy of phpThumb and extract it into your vendors directory. Should end up like: /vendors/phpThumb/{files}. (http://phpthumb.sourceforge.net)

## Usage
You need to first install MeioUpload 2.0 as a plugin. This is absolutely crucial as I have mentioned. MeioDuplicate is an extension of MeioUpload.

In a model that needs uploading, add MeioDuplicate.MeioDuplicate as behavior, like this:

	<?php
	class Image extends AppModel {
		var $name = 'Image';
		var $actsAs = array(
			'MeioDuplicate.MeioDuplicate' => array('filename')
		);
	}
	?>

Feel free to put in whatever MeioUpload options you need to set here into the MeioDuplicate.

You do not need to have MeioUpload anymore. Or you can rename MeioUpload to MeioDuplicate. Reason is because MeioDuplicate extends MeioUpload 2.0

No changes in database required.

Just use this code $this->duplicateImage($path) inside your Image model or $this->Image->duplicate($path) inside your ImageController.

$this->duplicateImage($path) where $path refers to the path of your image. The image will be copied to the directory listed in the dir option.
The thumbnails will be created as such.

The metadata will be inside $this->data. Just do a debug($this->data) after your $this->duplicateImage($path).

There is a test case inside the plugin. However, i do not know how to write a test case to check the behavior for the presence of the metadata.

I only know how to do it provided your cakeapp already has a model like that and you need to put in the Model name yourself. 

See the test case for more details under the commented portion.

## Saving data to database

Please note.

Since this is an extension of MeioUpload, the validation rules and functions used in the callbacks established by MeioUpload are still valid.

Hence if you wish to save the data AFTER running a duplicateImage($path), you need to do it by turning off the validate and callbacks option.

Eg, 


	<?php
	
	$duplicateSuccessful = $this->Image->duplicateImage($pathOfOriginalImage);

	if ($duplicateSuccessful) {
		// make some changes to data if you wish.
		$this->Image->data['Image']['cover']      = true;


		$data = $this->Image->data;

		// because we are creating new Image so we need to run this command.
		$this->Image->create();

		// because the MeioUpload behavior will generate errors in this form
		// [filename] => There were problems in uploading the file.
		// due to the function uploadCheckUploadError, hence we need to set the validate to false
		// we also need to set the callbacks to false because of the validations actions in the callbacks as well.
		$result = $this->Image->save($data, array('validate'=>false,
								 'callbacks'=>false));

	}
	?>