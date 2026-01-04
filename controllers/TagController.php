<?php

namespace app\controllers;

use Yii;
use app\models\Tag;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\data\ActiveDataProvider;

/**
 * TagController handles tag management.
 */
class TagController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            // Тільки автори можуть керувати тегами
                            return !Yii::$app->user->isGuest && 
                                   Yii::$app->user->identity->isAuthor();
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Tag models.
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Tag::find()->orderBy(['name' => SORT_ASC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Tag model.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Tag model.
     * If creation is successful, the browser will be redirected to the 'view' page or returnUrl if provided.
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new Tag();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Tag created successfully.');
            
            // Перевіряємо чи є returnUrl для перенаправлення
            $returnUrl = Yii::$app->request->post('returnUrl');
            if ($returnUrl) {
                return $this->redirect($returnUrl);
            }
            
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Tag model.
     * If update is successful, the browser will be redirected to the 'view' page or returnUrl if provided.
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Tag updated successfully.');
            
            // Перевіряємо чи є returnUrl для перенаправлення
            $returnUrl = Yii::$app->request->post('returnUrl');
            if ($returnUrl) {
                return $this->redirect($returnUrl);
            }
            
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Tag model.
     * If deletion is successful, the browser will be redirected to the 'index' page or returnUrl if provided.
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Перевіряємо, чи є статті з цим тегом
        if ($model->getArticlesCount() > 0) {
            Yii::$app->session->setFlash('error', 'Cannot delete tag with articles. Please remove tag from articles first.');
            
            // Перевіряємо чи є returnUrl
            $returnUrl = Yii::$app->request->get('returnUrl');
            if ($returnUrl) {
                return $this->redirect($returnUrl);
            }
            
            return $this->redirect(['index']);
        }
        
        $model->delete();
        Yii::$app->session->setFlash('success', 'Tag deleted successfully.');

        // Перевіряємо чи є returnUrl
        $returnUrl = Yii::$app->request->get('returnUrl');
        if ($returnUrl) {
            return $this->redirect($returnUrl);
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Tag model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return Tag the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Tag::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested tag does not exist.');
    }
}

