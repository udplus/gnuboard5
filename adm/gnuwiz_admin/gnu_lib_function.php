<?php
$sub_menu = "600010";
require_once './_common.php';

auth_check_menu($auth, $sub_menu, 'r');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

$g5['title'] = '그누보드 함수';
require_once G5_ADMIN_PATH.'/admin.head.php';
?>

    <div id="sidx_stock" class="tbl_head01 tbl_wrap">
        <table>
            <thead>
                <?php
                // lib폴더의 파일들을 스캔
                $scan = scandir(G5_PATH."/lib");
                $t = 0;
                echo '<tr>';
                foreach ($scan as $val) {
                    if (!strstr($val, ".lib.php")) continue; //파일명이 .lib.php로 끝나지않으면 무시

                    if ($t % 8 == 0) {
                        echo '</tr><tr>';
                    }

                    echo '<th scope="col"><a href="?file='.$val.'">'.$val.'</a></th>';

                    $t++;
                }
                echo '</tr>';
                ?>
            </thead>
        </table>
    </div>

<?php if ($_GET['file']) { ?>
<div class="tbl_head01 tbl_wrap">
    <table>
        <caption><?php echo $g5['title']; ?></caption>
        <colgroup>
            <col class="grid_4">
            <col>
            <col class="grid_4">
            <col>
        </colgroup>
        <thead>
        <tr>
            <th scope="col">라인</th>
            <th scope="col">함수명</th>
            <th scope="col">코멘트</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $file = file_get_contents(G5_PATH."/lib/".$_GET['file']);
        $scan = explode("\n", $file);
        $k = 1;
        for ($i = 0; $i < count($scan); $i++) {
            $line = $scan[$i];
            if (substr($line, 0, 8) != "function") {
                continue;
            }

            $name = trim(substr($line, 8));
            $name = trim(str_replace("{", "", $name));

            $comm = $scan[$i - 1];
            if (substr($comm, 0, 2) == "//") {
                $comm = trim($comm);
            }

            $k++;
            $bg = 'bg'.($k%2);
            ?>
            <tr class="<?php echo $bg; ?>">
                <td class="td_chk"><?php echo $i+1; ?></td>
                <td class="td_name txt_true"><?php echo $name; ?></td>
                <td class="td_name"><?php echo $comm; ?></td>
            </tr>
        <?php
        }

        if ($k == 1) {
            echo '<tr><td colspan="3" class="empty_table">함수가 없는 파일입니다.</td></tr>';
        }
        ?>
        </tbody>
    </table>
</div>
<?php } ?>

<div class="btn_fixed_top">
    <a href="<?php echo G5_URL ?>" class="btn btn_02">메인으로</a>
</div>

<?php
require_once G5_ADMIN_PATH.'/admin.tail.php';