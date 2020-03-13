<?php

namespace Yiisoft\Yii\Base\Web\Controllers;

use yii\web\Controller;
use yii\web\Response;
use yii\web\filters\AccessControl;
use yii\web\filters\VerbFilter;
use Yiisoft\Yii\Base\Web\Forms\LoginForm;
use Yiisoft\Yii\Base\Web\Forms\ContactForm;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                '__class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                '__class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['POST'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                '__class' => \yii\web\ErrorAction::class,
            ],
            'captcha' => [
                '__class' => \Yiisoft\Yii\Captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $this->app->getSession()->addFlash('info', 'Hello World! This is a flash message.');
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        $requiredPackages = [
            'rbac' => \Yiisoft\Rbac\Permission::class,
            #'db-mysql' => \Yiisoft\Db\Mysql\Schema::class,
            'yii-bootstrap4' => \Yiisoft\Yii\Bootstrap4\ActiveForm::class,
        ];
        $setupIncomplete = in_array(false, array_map('class_exists', $requiredPackages));

        if (!$setupIncomplete && !$this->app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load($this->app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
            'requiredPackages' => $requiredPackages,
            'setupIncomplete' => $setupIncomplete,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        $this->app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load($this->app->request->post()) && $model->contact($this->app->params['adminEmail'], $this->app->get('mailer'))) {
            $this->app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }

        $requiredPackages = [
            'yii-jquery' => '\Yiisoft\Yii\JQuery\ActiveFormClientScript',
            'yii-bootstrap4' => '\Yiisoft\Yii\Bootstrap4\ActiveForm',
            'yii-captcha' => '\Yiisoft\Yii\Captcha\Captcha',
        ];
        $setupIncomplete = in_array(false, array_map('class_exists', $requiredPackages));

        return $this->render('contact', [
            'model' => $model,
            'requiredPackages' => $requiredPackages,
            'setupIncomplete' => $setupIncomplete,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
