<?php
class LoginForm extends CFormModel
{
    public $name;
    public $password;
    public $rememberMe = false;
    
    private $_identity = null;
    
    public function rules()
    {
        return array(
            array('name,password','required','message' => 'wow','on' => 'login'),
            array('rememberMe','boolean','on' => 'login'),
            array('password','authenticate','on' => 'login')
        );
    }
    
    public function attributeLabels()
    {
        return array(
            'rememberMe'=>'Remember me next time',
            'name' => 'User name',
            'password' => 'User password'
        );
    }
    
    public function authenticate($attribute,$params)
    {
        $this->_identity = new CApplicationIdentity($this->name,$this->password);
        if (!$this->_identity->authenticate())
            $this->addError('password',Yii::t('system','Invalid username or password!'));
            
    }
    
    public function login()
    {
        if ($this->_identity === null)
        {
            $this->_identity = new CApplicationIdentity($this->name,$this->password);
            $this->_identity->authenticate();
        }
        if ($this->_identity->errorCode == CApplicationIdentity::ERROR_NONE)
        {
            $duration = ($this->rememberMe) ? 
                Yii::app()->settings->get('system','login.duration') : 0;
            
            Yii::app()->user->login($this->_identity,$duration);
            
            return true;
        }
        
        return false;
    }
}
?>