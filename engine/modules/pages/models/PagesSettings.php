<?php
/**
 * PagesSettings class file.
 *
 * @author Vlad Gramuzov <vlad.gramuzov@gmail.com>
 * @link http://yonote.org
 * @copyright 2014 Vlad Gramuzov
 * @license http://yonote.org/license.html
 */

/**
 * This model class allows to manage general pages configuration.
 * 
 * @author Vlad Gramuzov <vlad.gramuzov@gmail.com>
 * @since 1.0
 */
class PagesSettings extends CFormModel
{
    /**
     * @var int pages list page size (administrative panel).
     */
    public $adminPagesPageSize;
    /**
     * @var int maximum alias length.
     */
    public $aliasLengthMax;
    /**
     * @var int minumal alias length.
     */
    public $aliasLengthMin;
    /**
     * @var int minimal title length.
     */
    public $titleLengthMin;
    /**
     * @var int maximum title length.
     */
    public $titleLengthMax;
    
    public $thumbHeightMax;
    public $thumbHeightMin;
    public $thumbWidthMax;
    public $thumbWidthMin;
    public $thumbQuality;
    public $thumbResize;
    public $thumbResizeWidth;
    public $thumbResizeHeight;
    public $thumbSizeMax;
    
    private $_settings = array();
    private $_relations = array();
    
    /**
     * Validation rules.
     * @return array validation rules.
     */
    public function rules()
    {
        return array(
            array('thumbResize','boolean'),
            array(
                'adminPagesPageSize,aliasLengthMax,aliasLengthMin,titleLengthMin,titleLengthMax,thumbHeightMax,thumbHeightMin,thumbWidthMax,thumbWidthMin,thumbQuality,thumbResize,thumbResizeWidth,thumbResizeHeight,thumbSizeMax',
                'required',
                'message' => Yii::t('PagesModule.settings','model.pagessettings.error.required')
            ),
            array(
                'adminPagesPageSize,aliasLengthMax,aliasLengthMin,titleLengthMin,titleLengthMax,thumbHeightMax,thumbHeightMin,thumbWidthMax,thumbWidthMin,thumbResizeWidth,thumbResizeHeight,thumbSizeMax','numerical','integerOnly' => true,'min' => 1,
                'tooSmall' =>  Yii::t('PagesModule.settings','model.pagessettings.error.small'),
                'message' => Yii::t('PagesModule.settings','model.pagessettings.error.integer')
            ),
            array(
                'thumbQuality','numerical','integerOnly' => true,'min' => 1, 'max' => 100,
                'tooSmall' => Yii::t('PagesModule.settings','model.pagessettings.error.small'),
                'tooBig' => Yii::t('PagesModule.settings','model.pagessettings.error.big'),
                'message' => Yii::t('PagesModule.settings','model.pagessettings.error.integer')
            )
        );
    }
    
    /**
     * Save pages configuration.
     * @return boolean save is successfull.
     */
    public function save()
    {
        if (!$this->validate())
            return false;
        foreach ($this->_relations as $k => $v)
        {
            $model = Setting::model()->find('name=:name AND category=:cat',array(
                ':name' => $k,
                ':cat' => 'pages'
            ));
            if ($model !== null)
            {
                $model->setAttribute('value',$this->{$v});
                $model->save();
            }
        }
        return true;
    }
    
    /**
     * Attribute labels.
     * @return array attribute labels.
     */
    public function attributeLabels()
    {
        $labels = array();
        foreach ($this->_relations as $k => $v)
            $labels[$v] = Yii::t('PagesModule.settings',$this->_settings[$k]->description);
        return $labels;
    }
    
    /**
     * Load settings parameters to show.
     * @return void.
     */
    public function init()
    {
        $this->_relations = array(
            'admin.pages.page.size' => 'adminPagesPageSize',
            'alias.length.min' => 'aliasLengthMin',
            'alias.length.max' => 'aliasLengthMax',
            'title.length.min' => 'titleLengthMin',
            'title.length.max' => 'titleLengthMax',
            'thumbnail.height.max' => 'thumbHeightMax',
            'thumbnail.height.min' => 'thumbHeightMin',
            'thumbnail.width.max' => 'thumbWidthMax',
            'thumbnail.width.min' => 'thumbWidthMin',
            'thumbnail.resize.enabled' => 'thumbResize',
            'thumbnail.resize.width' => 'thumbResizeWidth',
            'thumbnail.resize.height' => 'thumbResizeHeight',
            'thumbnail.size.max' => 'thumbSizeMax',
            'thumbnail.quality' => 'thumbQuality'
        );
        
        $criteria = new CDbCriteria();
        $criteria->params = array(':category' => 'pages');
        $criteria->addInCondition('name',array_keys($this->_relations));
        $criteria->addCondition('category=:category');
        
        $settings = Setting::model()->findAll($criteria);

        foreach ($settings as $rec)
        {
            if (isset($this->_relations[$rec->name]))
                $this->{$this->_relations[$rec->name]} = $rec->value;
            $this->_settings[$rec->name] = $rec;
        }
    }
}
?>