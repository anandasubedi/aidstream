<?php

class Iati_Aidstream_Element_Activity_DefaultFinanceType extends Iati_Core_BaseElement
{
    protected $className = 'DefaultFinanceType';
    protected $displayName = 'Default Finance Type';
    protected $tableName = 'iati_default_finance_type';
    protected $attribs = array('id','text','@xml_lang','@code');
    protected $iatiAttribs = array('text','@xml_lang','@code');
}