<?php

/**
 *
 * 作成日：2017/07/17
 * 更新日：2018/08/19
 * 作成者：戸田滉洋
 * 更新者：戸田滉洋
 *
 */

/**
 * The BasicInfo Controller.
 *
 * @package app
 * @extends Controller
 */
class Controller_Electric_oneDayDemand extends Controller {

    public function before() {
        //未ログインの場合、ログインページにリダイレクト
        if (!Auth::check()) {
            Response::redirect('admin/login');
        }
    }

    public function action_index() {
        //一日分のデータを取得
        $oneday = Model_Electric::onedaydata();
        
        //天気予報情報を取得
        $params = Input::post();
        $onedaydate = Arr::get($params,'param_date_1');
        if(is_null($onedaydate)){
            $onedaydate = Arr::get($params,'onedaydate');
        }
        $twodaydate = Arr::get($params,'param_date_2');
        if(is_null($twodaydate)){
            $twodaydate = Arr::get($params,'twodaydate');
        }
        $second_graph_flag = Arr::get($params,'second_graph_flag');
        
        $oneday['weather_info'] = Model_Electric::getWeatherInfo($onedaydate,$twodaydate,$second_graph_flag);
        
        //テーマのインスタンス化
        $theme = \Theme::forge();
        //テーマにテンプレートのセット
        $theme->set_template('template');
        //テーマのテンプレートにタイトルをセット
        $theme->get_template()->set('title', 'MIETARO');
        //テーマのテンプレートにビューとページデータをセット
        $theme->get_template()->set('content', $theme->view('electric/onedaydemand')->set('onedayData',$oneday));
        //テーマのテンプレートにビューとページデータをセット
        $theme->get_template()->set('sidebar', $theme->view('sidebar'));
        return $theme;
    }

    public function post_getOnedayComment() {
        $strId = Input::post('str_id');
        $targetDate = Input::post('target_date');

        $result = Model_OnedayComment::getOnedayComment($strId, $targetDate);

        return json_encode($result);
    }

    public function post_addOnedayComment() {
        $strId = Input::post('str_id');
        $targetDate = Input::post('target_date');
        $comment = Input::post('comment');

        $result = Model_OnedayComment::addOnedayComment($strId, $targetDate,$comment);

        return $result;
    }

}
