<?php
/**
 *
 * 作成日：2017/1/11
 * 更新日：2017/1/23
 * 作成者：丸山　隼
 * 更新者：丸山　隼
 *
 */
/**
 * The Top Electric.
 *
 * 年間の詳細表示ページ
 * @package app
 * @extends Views
 */
?>

<h3><?php echo Session::get_flash('success', 'ようこそ' . Auth::get_screen_name() . 'さん'); ?></h3>
<ul class="nav nav-tabs">
    <li class="nav-item"><a href="oneDay">1日</a></li>
    <li class="nav-item"><a href="week">週間</a></li>
    <li class="nav-item"><a href="month">月間</a></li>
    <li class="nav-item"><a href="year">年間</a></li>
    <li class="nav-item"><a href="analysis">分析用</a></li>
</ul>

<?php echo Form::open(array('name' => 'yearinfo', 'method' => 'post', 'class' => 'form-horizontal')); ?>
    <table id="electric-data-table" class="table table-bordered">
        <tr>
            <th style="text-align:center;" colspan="4">
                メイン詳細<br/>
                <input type="date" name="param_date_1" value="param_date_1" id="form_param_date_1" style="width:150px; height:20px">
                <input class="btn btn-primary" name="submit" value="表示" type="submit" id="form_submit">
            </th>
            <th style="text-align:center;" colspan="3">
                比較対象詳細<br/>
                <input type="date" name="param_date_2" value="param_date_2" id="form_param_date_2" style="width:150px; height:20px">
                <input class="btn btn-primary" name="submit" value="表示" type="submit" id="form_submit">
            </th>
            <th colspan="3" style="text-align:center;">比較表</th>
        </tr>
        <tr>
            <th>  月 次  </th>
            <th>小計(kWh)</th>
            <th>最大デマンド値(kW)</th>
            <th>発生時刻</th>
            <th>小計(kWh)</th>
            <th>最大デマンド値(kW)</th>
            <th>発生時刻</th>
            <th>使用電力量(kwh)</th>
            <th>比率(％)</th>
        </tr>
    </table>
<?php echo Form::close(); ?>


<script>
    var electricData = <?php echo json_encode($electricData); ?>

    $('#form_param_date_1').val(electricData.param_date_1);
    $('#form_param_date_2').val(electricData.param_date_2);

//電力量テーブル作成
    var oneyearElectric = electricData.oneyear_electric;
    var twoyearElectric = electricData.twoyear_electric;
    var oneyearTotal = electricData.oneyear_total;
    var twoyearTotal = electricData.twoyear_total;
    var emission1 = electricData.total_emission_1;
    var emission2 = electricData.total_emission_2;
    var price1 = electricData.total_price_1;
    var price2 = electricData.total_price_2;
    var diffTotal = 0;

    $.each(oneyearElectric, function (key, value) {
        var diff = parseInt(twoyearElectric[key][0]) - parseInt(value[0]);
        diffTotal += diff;
        var diffStr = '';
        if (diff > 0) {
            diffStr = '<td> +' + diff + '</td>';
        }else{
        	diffStr = '<td>' + diff + '</td>';
        }

        $('#electric-data-table').append('<tr><td style="width:50px;">' + key + '</td><td>' + value[0] + '</td><td>' + value[1] + '</td><td>' + value[2] + '</td><td>' + twoyearElectric[key][0] + '</td><td>' + twoyearElectric[key][1] + '</td><td>' + twoyearElectric[key][2] + '</td>' + diffStr + '<td> - </td></tr>');
    });
    var diffTotalStr = '';
    if (diffTotal > 0) {
        diffTotalStr = '<td> +' + diffTotal + '</td>';
    } else {
        diffTotalStr = '<td>' + diffTotal + '</td>';
    }
    $('#electric-data-table').append('<tr><td style="width:50px;">  合計  </td><td>' + oneyearTotal + '</td><td> - </td><td>  </td><td>' + twoyearTotal + '</td><td> - </td><td>  </td>' + diffTotalStr + '<td> - </td></tr>');
    $('#electric-data-table').append('<tr><td style="width:50px;"> CO2排出量 </td><td colspan="3">' + emission1 + '<td colspan="3">' + emission2 + '</td><td colspan="2">-</td></tr>');
    $('#electric-data-table').append('<tr><td style="width:50px;"> 原油換算</td><td colspan="3">' + price1 + '<td colspan="3">' + price2 + '</td><td colspan="2">-</td></tr>');

</script>