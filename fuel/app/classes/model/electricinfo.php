<?php
/**
 *
 * 作成日：2017/1/12
 * 更新日：2017/1/23
 * 作成者：丸山　隼
 * 更新者：丸山　隼
 *
 */

/**
 * The Electric Model.
 *
 * 詳細表示用のモデルクラス
 *
 * @package app
 * @extends Model
 *
 *
 */
use Orm\Observer;
class Model_ElectricInfo extends \orm\Model {

    //DBへのセレクトクエリメソッド
    private static function selectElectricData($strId,$date_start,$date_end){
        $sql = "SELECT electric_at, str_id, electric_kw FROM Electric WHERE str_id = $strId AND electric_at >= '$date_start' AND electric_at <= '$date_end'";
        return \DB::query($sql)->execute()->as_array();
    }

    //一日使用電力詳細表示用データ取得
    public static function getOnedayData($strId,$onedayDate = "",$twodayDate = ""){

        //計算結果格納用配列
        $result = array(
            'oneday_date' => array(),
            'twoday_date' => array(),
            'oneday_total' => 0,
            'twoday_total' => 0,
            'param_date_1' => '',
            'param_date_2' => '',
        );

        //メイン
        if($onedayDate == ""){
            $onedayDate = date('Y-m-d');
        }
        //取得した日時の時間を整形
        $onedayStart = date('Y-m-d 00:00:00', strtotime($onedayDate));
        $onedayEnd = date('Y-m-d 23:59:59', strtotime($onedayDate));

            //時間計算用配列
            $targetTimeArray = array(
                'start' => array(),
                'end' => array(),
            );
            for($i=1;$i<=24;$i++){
                 $minutesStart = "+". 60 * ($i-1) . " minutes";
                $minutesEnd = "+". 60 * $i . " minutes";
                $targetTimeArray['start'][] = date('Y-m-d H:i:s', strtotime("$minutesStart -1 seconds",strtotime($onedayStart)));//よりも大きい
                $targetTimeArray['end'][] = date('Y-m-d H:i:s', strtotime("$minutesEnd -1 seconds",strtotime($onedayStart)));//以下
            }

            //対象の日付の電力情報を抽出
            $onedayElectricData = self::selectElectricData($strId, $onedayStart,$onedayEnd);

            //抽出した電力情報を60分毎に分割した合計値を配列に格納
            for($i=0;$i<=23;$i++){
                $result['oneday_date'] = array_merge($result['oneday_date'],array($targetTimeArray['end'][$i]=>0));
                $count = 0;
                foreach($onedayElectricData as $index => $data){
                    if(strtotime($targetTimeArray['start'][$i]) < strtotime($data['electric_at']) && strtotime($targetTimeArray['end'][$i]) >= strtotime($data['electric_at'])){
                        $count++;
                        $result['oneday_date'][$targetTimeArray['end'][$i]] += $data['electric_kw'];
                    }
                }
                if($count > 0){
                    $result['oneday_date'][$targetTimeArray['end'][$i]] = (int)($result['oneday_date'][$targetTimeArray['end'][$i]] / $count);
                    $result['oneday_total'] += (int)$result['oneday_date'][$targetTimeArray['end'][$i]];
                }
            }
            //テーブル表示用に配列のキーを書き換え
            $conArray = array();
            $key = 1;
            foreach($result['oneday_date'] as $data){
                if($key < 10){
                    $arrayKey = '~ 0'.$key.':00';
                }else{
                    $arrayKey = '~ '.$key.':00';
                }
                $conArray = array_merge($conArray,array($arrayKey=>$data));
                $key++;
            }
            $result['oneday_date'] = $conArray;

        //比較用
        if($twodayDate == ""){
            $twodayDate = date('Y-m-d',strtotime('-1 days'));
        }

        $twodayStart = date('Y-m-d 00:00:00', strtotime($twodayDate));
        $twodayEnd = date('Y-m-d 23:59:59', strtotime($twodayDate));

            //時間計算用配列
            $targetTimeArray = array(
                'start' => array(),
                'end' => array(),
            );
            for($i=1;$i<=24;$i++){
                $minutesStart = "+". 60 * ($i-1) . " minutes";
                $minutesEnd = "+". 60 * $i . " minutes";
                $targetTimeArray['start'][] = date('Y-m-d H:i:s', strtotime("$minutesStart -1 seconds",strtotime($twodayStart)));//よりも大きい
                $targetTimeArray['end'][] = date('Y-m-d H:i:s', strtotime("$minutesEnd -1 seconds",strtotime($twodayStart)));//以下
            }

            //対象の日付の電力情報を抽出
            $twodayElectricData = self::selectElectricData($strId, $twodayStart,$twodayEnd);

            //抽出した電力情報を60分毎に分割した合計値を配列に格納
            for($i=0;$i<=23;$i++){
                $result['twoday_date'] = array_merge($result['twoday_date'],array($targetTimeArray['end'][$i]=>0));
                $count=0;
                foreach($twodayElectricData as $index => $data){
                    if(strtotime($targetTimeArray['start'][$i]) < strtotime($data['electric_at']) && strtotime($targetTimeArray['end'][$i]) >= strtotime($data['electric_at'])){
                        $count++;
                        $result['twoday_date'][$targetTimeArray['end'][$i]]+= $data['electric_kw'];
                    }
                }
                if($count > 0){
                    $result['twoday_date'][$targetTimeArray['end'][$i]] = (int)($result['twoday_date'][$targetTimeArray['end'][$i]]  / $count);
                    $result['twoday_total'] += (int)$result['twoday_date'][$targetTimeArray['end'][$i]];
                }
            }

            //テーブル表示用に配列のキーを書き換え
            $conArray = array();
            $key = 1;
            foreach($result['twoday_date'] as $data){
                if($key < 10){
                    $arrayKey = '~ 0'.$key.':00';
                }else{
                    $arrayKey = '~ '.$key.':00';
                }
                $conArray = array_merge($conArray,array($arrayKey=>$data));
                $key++;
            }
            $result['twoday_date'] = $conArray;

            $result['param_date_1'] = date('Y-m-d',strtotime($onedayStart));
            $result['param_date_2'] = date('Y-m-d',strtotime($twodayStart));

        return $result;
    }


    //週間使用電力詳細表示用データ取得
    public static function getWeekData($strId,$oneweekDate = "",$twoweekDate = ""){

        //計算結果格納用配列
        $result = array(
            'oneweek_date' => array(),
            'twoweek_date' => array(),
            'oneweek_total' => 0,
            'twoweek_total' => 0,
            'param_date_1' => '',
            'param_date_2' => '',
        );

        $result1 = array();
        $result2 = array();

        //メイン
        if($oneweekDate == ""){
            $oneweekDate = date('Y-m-d');
        }
        $oneweekStart = date('Y-m-d 00:00:00', strtotime("-1 week",strtotime($oneweekDate)));
        $oneweekEnd = date('Y-m-d 23:59:59', strtotime($oneweekDate));

        $result1 = self::selectElectricData($strId, $oneweekStart,$oneweekEnd);
        //比較
        if($twoweekDate == ""){
            $twoweekDate = date('Y-m-d',strtotime('-1 week',strtotime($oneweekDate)));
        }
        $twoweekStart = date('Y-m-d 00:00:00', strtotime("-1 week",strtotime($twoweekDate)));
        $twoweekEnd = date('Y-m-d 23:59:59', strtotime($twoweekDate));

        $result2= self::selectElectricData($strId, $twoweekStart,$twoweekEnd);
        //週間使用電力取得
        $convertResult = \Model_Electric::convertDataForWeek($result1,$result2,$oneweekDate,$twoweekDate);

        $oneweekDateArray = $convertResult['one_week'][0];
        $twoweekDateArray = $convertResult['two_week'][0];


        $tmpDate = date('Y-m-', strtotime($oneweekDate));
        $week = array('日','月','火','水','木','金','土');

        foreach($oneweekDateArray as $index=>$date){
                if($index==0){continue;}
                $calcDateArray=explode('-',$date);
                $tmpDateTime = new DateTime($tmpDate.$calcDateArray[2]);
                $w = (int)$tmpDateTime->format('w');

                $result['oneweek_date'] = array_merge($result['oneweek_date'],array($calcDateArray[2].'日('."$week[$w]".')'=>0));

                foreach($convertResult['one_week'] as $key=>$dataArray){
                      if($key==0){continue;}

                      $result['oneweek_date'][$calcDateArray[2].'日('."$week[$w]".')'] += (int)$dataArray[$index];
                      $result['oneweek_total'] += (int)$dataArray[$index];
                }
        }



        $tmpDate = date('Y-m-', strtotime($twoweekDate));
        foreach($twoweekDateArray as $index=>$date){
                if($index==0){continue;}
                $calcDateArray=explode('-',$date);
                $tmpDateTime = new DateTime($tmpDate.$calcDateArray[2]);
                $w = (int)$tmpDateTime->format('w');

                $result['twoweek_date'] = array_merge($result['twoweek_date'],array($calcDateArray[2].'日('."$week[$w]".')'=>0));

                foreach($convertResult['two_week'] as $key=>$dataArray){
                    if($key==0){continue;}
                    $result['twoweek_date'][$calcDateArray[2].'日('."$week[$w]".')'] += (int)$dataArray[$index];
                    $result['twoweek_total'] += (int)$dataArray[$index];
                }
        }

            $result['param_date_1'] = $oneweekDate;
            $result['param_date_2'] = $twoweekDate;
        return $result;
    }


    //週間使用電力詳細表示用データ取得
    public static function getMonthData($strId,$onemonthDate = ""){

        //計算結果格納用配列
        $result = array(
            'onemonth_date' => array(),
            'onemonth_total' => 0,
        );

        $result1 = array();

        //メイン
        if($onemonthDate == ""){
            $onemonthDate = date('Y-m-d');
        }
        $onemonthStart = date('Y-m-1 00:00:00', strtotime($onemonthDate));
        $onemonthEnd = date('Y-m-d 23:59:59', strtotime("-1 days ",strtotime(date('Y-m-1 00:00:00', strtotime("+1 MONTH ",strtotime($onemonthStart))))));

        $result1 = self::selectElectricData($strId, $onemonthStart,$onemonthEnd);

        //日毎に電力情報を取得
        $convertResult = Model_Electric::convertDataForMonth($result1,array(),$onemonthStart,$onemonthEnd,"","",0);

        $tmpDate = date('Y-m-', strtotime($onemonthDate));
        $week = array('日','月','火','水','木','金','土');
        foreach($convertResult['result'] as $index=>$arrayData){
            if($index == 0){continue;}
            //曜日を計算
            $tmpDateTime = new DateTime($tmpDate.$arrayData[0]);
            $w = (int)$tmpDateTime->format('w');

            $result['onemonth_date'] = array_merge($result['onemonth_date'],array($arrayData[0]."($week[$w])"=>(int)$arrayData[1]));
        }
        $result['onemonth_total'] = $convertResult['total_one_month'];

        $result['param_date_1'] = $onemonthDate;

        return $result;
    }


    //週間使用電力詳細表示用データ取得
    public static function getYearData($strId,$oneyearDate = "",$twoyearDate = ""){

        //計算結果格納用配列
        $result = array(
            'oneyear_date' => array(),
            'twoyear_date' => array(),
            'oneyear_total' => 0,
            'twoyear_total' => 0,
        );

        //メイン
        if($oneyearDate == ""){
            $oneyearDate = date('Y-m-d');
        }
        $oneyearStart = date('Y-01-01 00:00:00', strtotime($oneyearDate));
        $oneyearEnd = date('Y-12-31 23:59:59', strtotime($oneyearDate));

        $result1 = self::selectElectricData($strId, $oneyearStart,$oneyearEnd);
        //比較
        $result2 = array();
        $checkedFlg = 0;
        $twoyearStart = "";
        $twoyearEnd = "";
        if($twoyearDate == ""){
            $twoyearDate = date('Y-m-d',strtotime('-1 years'));
        }
        $twoyearStart = date('Y-01-01 00:00:00', strtotime($twoyearDate));
        $twoyearEnd = date('Y-12-31 23:59:59', strtotime($twoyearDate));

        $result2 = self::selectElectricData($strId, $twoyearStart,$twoyearEnd);
        //月毎に電力情報を取得
        $convertResult = Model_Electric::convertDataForYear($result1,$result2,$oneyearStart,$oneyearEnd,$twoyearStart,$twoyearEnd,1);

             foreach($convertResult['result'] as $index=>$arrayData){
                if($index == 0){continue;}
                $result['oneyear_date'] = array_merge($result['oneyear_date'],array($arrayData[0]."月"=>(int)$arrayData[1]));
            }

            foreach($convertResult['result'] as $index=>$arrayData){
                if($index == 0){continue;}
                $result['twoyear_date'] = array_merge($result['twoyear_date'],array($arrayData[0]."月"=>(int)$arrayData[2]));
            }

        $result['oneyear_total'] = $convertResult['total_one_year'];
        $result['twoyear_total'] = $convertResult['total_two_year'];

        $result['param_date_1'] = $oneyearDate;
        $result['param_date_2'] = $twoyearDate;

        return $result;
    }
}
