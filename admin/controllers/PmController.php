<?php
/**
 * PmController class file.
 *
 * @author Vlad Gramuzov <vlad.gramuzov@gmail.com>
 * @link http://yonote.org
 * @copyright 2014 Vlad Gramuzov
 * @license http://yonote.org/license.html
 */

/**
 * Personal messages manager controller, used in administrative panel.
 * 
 * @author Vlad Gramuzov <vlad.gramuzov@gmail.com>
 * @since 1.0
 */
class PmController extends CApplicationController
{
    /**
     * Controller filters.
     * @return array filters.
     */
    public function filters()
    {
        return array(
            'accessControl'
        );
    }
    
    /**
     * Controller access rules.
     * @return array access rules.
     */
    public function accessRules()
    {
        return array(
            array(
                'allow',
                'actions' => array('index'),
                'roles' => array('admin.pm.index')
            ),
            array(
                'allow',
                'actions' => array('settings'),
                'roles' => array('admin.pm.settings')
            ),
            array(
                'allow',
                'actions' => array('read'),
                'roles' => array('admin.pm.read')
            ),
            array(
                'allow',
                'actions' => array('remove'),
                'roles' => array('admin.pm.remove')
            ),
            array(
                'allow',
                'actions' => array('outbox'),
                'roles' => array('admin.pm.outbox')
            ),
            array(
                'allow',
                'actions' => array('new'),
                'roles' => array('admin.pm.new')
            ),
            array(
                'deny',
                'users' => array('*')
            )
        );
    }
    
    /**
     * PM settings.
     * @return void.
     */
    public function actionSettings()
    {
        $this->pageTitle = Yii::t('pm','page.settings.title');
        $this->setPathsQueue(array(
            Yii::t('pm','page.settings.title') => $this->createUrl($this->getRoute())
        ));
        $model = new PmSettings();
        if (isset($_POST['PmSettings']))
        {
            $model->setAttributes($_POST['PmSettings']);
            if ($model->save())
            {
                Yii::app()->user->setFlash(
                    'pmSettingsSuccess',
                    Yii::t('pm','success.settings.update')
                );
                $this->refresh();
            }
        }
        $this->render('settings',array(
            'model' => $model
        ));
    }
    
    /**
     * Inbox messages.
     * @return void.
     */
    public function actionIndex()
    {
        $this->pageTitle = Yii::t('pm','page.pm.title');
        $this->setPathsQueue(array(
            Yii::t('pm','page.pm.title') => Yii::app()->createUrl($this->getRoute())
        ));
        $pmList = $this->loadPmListModel();
        $this->render('index',array(
            'models' => $pmList->models,
            'sort' => $pmList->sort
        ));
    }
    
    /**
     * Read message.
     * @param int $id message id.
     * @throws CHttpException if message not found or invalid request given.
     */
    public function actionRead($id)
    {
        $this->pageTitle = Yii::t('pm','page.read.title');
        $this->setPathsQueue(array(
            Yii::t('pm','page.pm.title') => Yii::app()->createUrl('pm/index'),
            Yii::t('pm','page.read.title') => Yii::app()->createUrl($this->getRoute())
        ));
        $model = Pm::model()->find('ownerid=:ownerid AND id=:id',array(
            ':ownerid' => Yii::app()->user->getId(),
            ':id' => (int) $id
        ));
        if ($model == null)
            throw new CHttpException(404,Yii::t('system','error.404.description'));
        $model->read();
        $model->save();
        $this->render('read',array(
            'model' => $model
        ));
    }
    
    /**
     * Removes selected messages.
     * @return void.
     */
    public function actionRemove()
    {
        $c = 0;
        if (isset($_REQUEST['select']) && is_array($_REQUEST['select']))
        {
            $criteria = new CDbCriteria();
            $criteria->params = array(':ownerid' => Yii::app()->user->getId());
            $criteria->addInCondition('id',$_REQUEST['select'],'OR');
            $criteria->addCondition('ownerid=:ownerid');
            $c = Pm::model()->deleteAll($criteria);
        }
        if ($c == 0)
            Yii::app()->user->setFlash('pmWarning',Yii::t('pm','warning.messages.remove'));
        else
            Yii::app()->user->setFlash('pmSuccess',Yii::t('pm','success.messages.remove'));
        $this->redirect(array('index'));
    }
    
    /**
     * Show outbox.
     * @return void.
     */
    public function actionOutbox()
    {
        $this->pageTitle = Yii::t('pm','page.outbox.title');
        $this->setPathsQueue(array(
            Yii::t('pm','page.pm.title') => Yii::app()->createUrl('pm/index'),
            Yii::t('pm','page.outbox.title') => Yii::app()->createUrl($this->getRoute())
        ));
        $pmList = $this->loadPmListModel(false);
        $this->render('outbox',array(
            'models' => $pmList->models,
            'sort' => $pmList->sort
        ));
    }
    
    /**
     * Add new message.
     * @return void.
     * @throws CHttpException if invalid replyid given.
     */
    public function actionNew()
    {
        $this->pageTitle = Yii::t('pm','page.add.title');
        $this->setPathsQueue(array(
            Yii::t('pm','page.pm.title') => Yii::app()->createUrl('pm/index'),
            Yii::t('pm','page.add.title') => Yii::app()->createUrl($this->getRoute())
        ));
        $model = new Pm('inbox');
        if (isset($_GET['replyid']))
        {
            $reply = Pm::model()->find('ownerid=:ownerid AND id=:id',array(
                ':ownerid' => Yii::app()->user->getId(),
                ':id' => $_GET['replyid']
            ));
            if ($reply !== null)
            {
                $re = mb_substr($reply->title,0,3);
                $model->title = ($re == 'RE:') ? $reply->title : "RE: {$reply->title}";
                $model->touserid = $reply->author->name;
                $model->message = CHtml::tag('blockquote',array(),$reply->message);
                $model->message .= CHtml::tag('p');
            }
            else
                throw new CHttpException(400,Yii::t('system','error.400.description'));
        }
        else
        {
            if (isset($_GET['to']))
                $model->touserid = $_GET['to'];
        }
        if (isset($_POST['Pm']))
        {
            $model->setAttributes($_POST['Pm']);
            $model->setSenderId(Yii::app()->user->getId());
            $model->inbox();
            if ($model->save())
            {
                $owner = new Pm('outbox');
                $owner->setAttributes($_POST['Pm']);
                $owner->setOwnerId(Yii::app()->user->getId());
                $owner->setSenderId(Yii::app()->user->getId());
                $owner->outbox();
                $owner->save();
                Yii::app()->user->setFlash('pmSuccess',Yii::t('pm','success.messages.sent'));
                $this->redirect(array('index'));
            }
        }
        $this->render('new',array(
            'model' => $model
        ));
    }
    
    /**
     * Load PM list with specified conditions.
     * @param boolean $inbox true for inbox, false for outbox messages.
     * @return CAttributeCollection of models and sort.
     */
    public function loadPmListModel($inbox = true)
    {
        $return = new CAttributeCollection();
        $sort = new CSort();
        
        $sort->sortVar = 'sort';
        $sort->defaultOrder = 'updatetime DESC';
        $sort->multiSort = true;

        $sort->attributes = array(
            'title' => array(
                'label' => Yii::t('pm','model.pm.title'),
                'asc' => 'title ASC',
                'desc' => 'title DESC',
                'default' => 'asc',
            ),
            'senderid' => array(
                'label' => Yii::t('pm','model.pm.senderid'),
                'asc' => 'senderid ASC',
                'desc' => 'senderid DESC',
                'default' => 'asc',
            ),
            'updatetime' => array(
                'label' => Yii::t('pm','model.pm.updatetime'),
                'asc' => 'updatetime ASC',
                'desc' => 'updatetime DESC',
                'default' => 'asc'
            )
        );

        $criteria = new CDbCriteria();
        $criteria->params = array(':ownerid' => Yii::app()->user->getId());
        
        if (isset($_POST['search']))
            Yii::app()->session['pmSearch'] = $_POST['search'];
        if (Yii::app()->session['pmSearch'] !== null){
            $criteria->addSearchCondition('title',Yii::app()->session['pmSearch'],true);
            $criteria->addSearchCondition('message',Yii::app()->session['pmSearch'],true,'OR');
        }
        
        if ($inbox)
        {
            $criteria->addCondition('inbox=1');
            $criteria->addCondition('outbox=0');
        }
        else
        {
            $criteria->addCondition('outbox=1');
            $criteria->addCondition('inbox=0');
        }
        
        $criteria->addCondition('ownerid=:ownerid');

        $sort->applyOrder($criteria);
        $return->models = Pm::model()->findAll($criteria);
        $return->sort = $sort;
        
        return $return;
    }
}
?>