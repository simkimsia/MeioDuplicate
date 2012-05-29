<?php
/**
 * MeioDuplicate Behavior
 *
 * This behavior extends jrbasso's MeioUpload 3.0
 * http://github.com/jrbasso/MeioUpload
 *
 * @author Sim Kim Sia (kimcity@gmail.com)
 * @package app
 * @subpackage app.models.behaviors
 * @filesource
 * @version 0.1
 * @lastmodified 2011-09-29
 */
App::import('Behavior', 'MeioUpload.MeioUpload');

App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class MeioDuplicateBehavior extends MeioUploadBehavior {


/**
 * Duplicate an existing file given a path to the file.
 *
 * @author Sim Kim Sia
 */
	public function duplicateImage(&$model, $img) {

		// file must exist before proceeding
		if (!file_exists($img)) {
			return false;
		}

		$fileName 	= basename($img);

		$data 		= $model->data;
		$return 	= array();

		foreach ($this->__fields[$model->alias] as $fieldName => $options) {

			//Create the appropriate directory and thumbnails directories.
			$this->_createFolders($options['dir'],'thumb', array_keys($options['thumbsizes']), 0755);

			// retrieve extension
			list($file, $ext) = $this->_splitFilenameAndExt($fileName);

			// fix the name
			// if we do not use table, then we just overwrite the file else we change the filename from abc.jpg to abc-1.jpg for eg.
			$fileName = $this->_fixDuplicateName($model, $fileName, $fieldName, $options['useTable']);

			// path to save this particular duplicate
			// we added the app_www_root to allow successful save
			$saveAs = $options['dir'] . DS . $fileName;
		
			// duplicate the file now in app's webroot folder
			$result = copy($img, WWW_ROOT . $saveAs);

			// if UNsuccessfully copied
			if (!$result){
				return false;
			}
			
			// this must point to the one in app folder not in mainsite folder
			$pathToDestinationFile = WWW_ROOT . $saveAs;
			
			// to ascertain that the file here is really an image file
			// only suitable for >= PHP 5.3.0
			$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
			$type  = finfo_file($finfo, $pathToDestinationFile);
			finfo_close($finfo);

			if (!empty($options['thumbsizes']) && !empty($options['allowedExt']) && in_array($type, $this->_imageTypes)) {
				
				$this->_createThumbnailsForDuplicate($model, $fieldName, $saveAs, $ext, $options);
			}

			// now we set the values to the model's data array in case the useTable is true.
			// Update model data
			if ($model->useTable !== false) {
				$data[$model->alias][$options['fields']['dir']] 		= $options['dir'];
				$data[$model->alias][$options['fields']['mimetype']] 	= $type;
				$data[$model->alias][$options['fields']['filesize']] 	= filesize($pathToDestinationFile);
				$data[$model->alias][$fieldName] 						= $fileName;
				// now we put the file info back into ProductImage->data
				$model->data = $data;
			}

		}

		return true;

	}

/**
 * Removes the bad characters from the $filename. It updates the $model->data.
 *
 * @param object $model
 * @param string $fieldName
 * @param boolean $checkFile
 * @return void
 */
	protected function _fixDuplicateName(&$model, $fileName, $fieldName, $checkFile = true) {
		list ($filename, $ext) 	= $this->_splitFilenameAndExt($fileName);
		$filename 				= Inflector::slug($filename, '-');

		$i 						= 0;
		$newFilename 			= $filename;

		if ($checkFile) {
			while (file_exists(WWW_ROOT . $this->__fields[$model->alias][$fieldName]['dir'] . DS . $newFilename . '.' . $ext)) {
			
				$newFilename = $filename . '-' . $i++;
			}
		}
		
		return $newFilename . '.' . $ext;
	}


/**
 * Create all the thumbnails for duplicateImage
 *
 * @param object $model
 * @param string $fieldName
 * @param string $saveAs
 * @param string $ext
 * @param array $options
 * @return void
 */
	protected function _createThumbnailsForDuplicate(&$model, $fieldName, $saveAs, $ext, $options) {
		foreach ($options['thumbsizes'] as $key => $value) {
			// Generate the name for the thumbnail
			$thumbSaveAs = WWW_ROOT . $options['dir'] . DS . 'thumb' . DS . $key . DS . basename($saveAs);

			$params = array();
			if (isset($value['width'])) {
				$params['thumbWidth'] = $value['width'];
			}
			if (isset($value['height'])) {
				$params['thumbHeight'] = $value['height'];
			}
			if (isset($value['maxDimension'])) {
				$params['maxDimension'] = $value['maxDimension'];
			}
			if (isset($value['thumbnailQuality'])) {
				$params['thumbnailQuality'] = $value['thumbnailQuality'];
			}
			if (isset($value['zoomCrop'])) {
				$params['zoomCrop'] = $value['zoomCrop'];
			}
			$this->_createThumbnail($model, $saveAs, $thumbSaveAs, $fieldName, $params);
		}
	}


}
?>