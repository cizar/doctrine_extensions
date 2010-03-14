<?php

class Doctrine_Template_Listener_HasFile extends Doctrine_Record_Listener
{
	/**
	 * Array of behavior options
	 *
	 * @var string
	 */
	protected $_options = array();

	/**
	 * __construct
	 *
	 * @param string $options 
	 * @return void
	 */
	public function __construct(array $options)
	{
		$this->_options = $options;
	}

    /**
     * Post Save Hook - Si se asigno un archivo lo mueve al destino
     * 
     * @return null
     */
    public function postSave(Doctrine_Event $event)
    {
		$event->getInvoker()->moveUploadedFile();
    }
    
	/**
	 * Post Delete Hook - Borra el archivo del disco
	 * 
	 * @param Doctrine_Event $event
	 * @return null
	 */
	public function postDelete(Doctrine_Event $event)
	{
		$event->getInvoker()->removeFile();
	}
}