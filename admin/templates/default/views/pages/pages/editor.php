<?php
$tinymce = Yii::app()->assetManager->publish(
    Yii::getPathOfAlias('application.vendors.tinymce')
);
$tinymcePath = Yii::app()->assetManager->getPublishedPath(
    Yii::getPathOfAlias('application.vendors.tinymce')
);
$tinymceLangs = scandir($tinymcePath.'/langs');
$tinymceLanguage = (in_array(Yii::app()->getLanguage().'.js',$tinymceLangs)) ? Yii::app()->getLanguage() : '';
?>

<script type="text/javascript" src="<?php echo $tinymce; ?>/tinymce.min.js"></script>
<script type="text/javascript">
tinymce.init({
    selector: 'textarea',
    language: '<?php echo $tinymceLanguage; ?>',
    plugins: [
        'link','image','code','media'
    ]
 });
</script>

<?php if (Yii::app()->user->hasFlash('pagesSuccess')): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-success">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <?php echo Yii::app()->user->getFlash('pagesSuccess'); ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            
            <?php echo CHtml::form(null,'POST',array(
                'role' => 'form',
                'id' => 'pagesForm'
            )); ?>
            
                <div class="panel-body"> 
                    <div class="form-group <?php if ($model->hasErrors('alias')) echo('has-error'); ?>">
                        <?php echo CHtml::activeLabel($model,'alias',array(
                            'for' => 'pageAlias',
                            'class' => 'control-label'
                        )); ?>
                        <?php echo CHtml::activeTextField($model,'alias',array(
                            'class' => 'form-control',
                            'id' => 'pageAlias',
                            'placeholder' => Yii::t('PagesModule.pages','placeholder.page.alias')
                        )); ?>
                        <?php echo CHtml::error($model,'alias',array(
                            'class' => 'help-block text-danger'
                        )); ?>
                    </div>

                    <div class="form-group <?php if ($model->hasErrors('title')) echo('has-error'); ?>">
                        <?php echo CHtml::activeLabel($model,'title',array(
                            'for' => 'pageTitle',
                            'class' => 'control-label'
                        )); ?>
                        <?php echo CHtml::activeTextField($model,'title',array(
                            'class' => 'form-control',
                            'id' => 'pageTitle',
                            'placeholder' => Yii::t('PagesModule.pages','placeholder.page.title')
                        )); ?>
                        <?php echo CHtml::error($model,'title',array(
                            'class' => 'help-block text-danger'
                        )); ?>
                    </div>

                    <div class="form-group <?php if ($model->hasErrors('language')) echo('has-error'); ?>">
                        <?php echo CHtml::activeLabel($model,'language',array(
                            'for' => 'pageLanguage',
                            'class' => 'control-label'
                        )); ?>
                        <?php echo CHtml::activeDropDownList($model,'language',array_merge(array(''=>''),$model->getLanguages()),array(
                            'class' => 'form-control',
                            'id' => 'pageLanguage'
                        )); ?>
                        <?php echo CHtml::error($model,'language',array(
                            'class' => 'help-block text-danger'
                        )); ?>
                    </div>

                    <div class="form-group <?php if ($model->hasErrors('content')) echo('has-error'); ?>">
                        <?php echo CHtml::activeLabel($model,'content',array(
                            'for' => 'pageContent',
                            'class' => 'control-label'
                        )); ?>
                        <?php echo CHtml::activeTextArea($model,'content',array(
                            'class' => 'form-control',
                            'id' => 'pageContent'
                        )); ?>

                        <?php echo CHtml::error($model,'content',array(
                            'class' => 'help-block text-danger'
                        )); ?>
                    </div>
                </div>
                <div class="panel-footer">
                    <button type="submit" class="btn btn-primary"><?php echo Yii::t('system','app.save'); ?></button>
                    <button type="reset" class="btn btn-default"><?php echo Yii::t('system','app.reset'); ?></button>
                </div>
            
            <?php echo CHtml::endForm(); ?>
        </div>
    </div>
</div>