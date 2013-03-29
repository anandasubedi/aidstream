<?php

class Iati_Aidstream_Element_Activity_DocumentLink_Language extends Iati_Core_BaseElement
{   
    protected $isMultiple = true;
    protected $className = 'Language';
    protected $displayName = 'Language';
    protected $attribs = array('id' , 'text');
    protected $iatiAttribs = array('text');
    protected $tableName = 'iati_document_link/language';
}