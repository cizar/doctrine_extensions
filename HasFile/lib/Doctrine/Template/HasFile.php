<?php

class Doctrine_Template_HasFile extends Doctrine_Template
{
	protected $_options = array(
		'name'              =>  'file_path',
		'alias'             =>  null,
		'type'              =>  'string',
		'length'            =>  255,
		'options'           =>  array(),
		'valid_extensions'  => array());
		
	private static $_upload_path = null;
	
	private $_uploaded_file = null;
	
	public static function setUploadPath($path)
	{
		$real_path = realpath($path);
		
		if (!$real_path)
		{
			throw new Exception("The path '{$path}' not exist");
		}
		
		if (!is_dir($real_path))
		{
			throw new Exception("The path '{$path}' is not a directory");
		}
		
		if (!is_writable($real_path))
		{
			throw new Exception("The directory '{$path}' is not writable");
		}
		
		self::$_upload_path = $real_path;
	}
	
	public static function getUploadPath()
	{
		if (null === self::$_upload_path)
		{
			throw new Exception("The upload path is undefined");
		}

		return self::$_upload_path;
	}

	/**
	 * Setup the HasFile behavior for the template
	 *
	 * @return void
	 */
	public function setUp()
	{
		$this->addListener(new Doctrine_Template_Listener_HasFile($this->_options));
	}

	/**
	 * Set table definition for HasFile behavior
	 *
	 * @return void
	 */
	public function setTableDefinition()
	{
		$name = $this->_options['name'];

		if ($this->_options['alias'])
		{
			$name .= ' as ' . $this->_options['alias'];
		}

		$this->hasColumn($name, $this->_options['type'], $this->_options['length'], $this->_options['options']);
	}

	/**
	 * TODO: Documentar
	 *
	 * @param array $file
	 * @return void
	 */
	public function assignFile($file)
	{
		if ($file['error'])
		{
			throw new Doctrine_Record_Exception('Can not assign the uploaded file because it contains errors');
		}

		$filename = $file['name'];
		
		$info = pathinfo($filename);
		
		$attempts = 0;
		
		while (file_exists(self::getUploadPath() . '/' . $filename))
		{
			$filename = $info['filename'] . '_' . $attempts++ . '.' . $info['extension'];
		}

		$this->_uploaded_file = $file['tmp_name'];

		$record = $this->getInvoker();
		
		$field_name = $record->getTable()
			->getFieldName($this->_options['name']);

		$record->$field_name = $filename;
	}
	
	public function moveUploadedFile()
	{
		if (!$this->_uploaded_file)
			return false;
			
		if (!@move_uploaded_file($this->_uploaded_file, $this->getFileFullPath()))
		{
			throw new Doctrine_Record_Exception('Can not move the uploaded file');
		}
	}
	
	public function removeFile()
	{
		unlink($this->getFileFullPath());
	}
	
	public function getFileFullPath()
	{
		$record = $this->getInvoker();

		$field_name = $record->getTable()
			->getFieldName($this->_options['name']);

		return self::getUploadPath() . '/' . $record->$field_name;
	}
}