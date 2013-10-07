<?php
$sub_menu = "100200";
include_once('./_common.php');

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

$token = get_token();

$sql_common = " from {$g5['auth_table']} a left join {$g5['member_table']} b on (a.mb_id=b.mb_id) ";

$sql_search = " where (1) ";
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst  = "a.mb_id, au_menu";
    $sod = "";
}
$sql_order = " order by $sst $sod ";

$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search}
            {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$listall = '';
if ($sfl || $stx) // 검색렬일 때만 처음 버튼을 보여줌 : 지운아빠 2012-10-31
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';

$g5['title'] = "관리권한설정";
include_once('./admin.head.php');

$colspan = 5;
?>

<form name="fsearch" id="fsearch" method="get">
<input type="hidden" name="sfl" value="a.mb_id" id="sfl">
<fieldset>
    <legend>관리권한 검색</legend>
    <span>
        <?php echo $listall ?>
        설정된 관리권한 <?php echo number_format($total_count) ?>건
    </span>
    <strong id="msg_stx" class="msg_sound_only"></strong>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" title="회원아이디(필수)" required class="required frm_input">
    <input type="submit" value="검색" id="fsearch_submit" class="btn_submit">
</fieldset>
</form>

<section class="cbox">
    <h2>설정된 관리권한 내역</h2>
    <p>권한 <strong>r</strong>은 읽기권한, <strong>w</strong>는 쓰기권한, <strong>d</strong>는 삭제권한입니다.</p>

    <ul class="sort_odr">
        <li><?php echo subject_sort_link('a.mb_id') ?>회원아이디<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('mb_nick') ?>별명<span class="sound_only"> 순 정렬</span></a></li>
    </ul>

    <form name="fauthlist" id="fauthlist" method="post" action="./auth_list_delete.php" onsubmit="return fauthlist_submit(this);">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="<?php echo $token ?>">
    <table>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">회원아이디</th>
        <th scope="col">별명</th>
        <th scope="col">메뉴</th>
        <th scope="col">권한</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $mb_nick = get_sideview($row['mb_id'], $row['mb_nick'], $row['mb_email'], $row['mb_homepage']);

        // 메뉴번호가 바뀌는 경우에 현재 없는 저장된 메뉴는 삭제함
        if (!isset($auth_menu[$row['au_menu']]))
        {
            sql_query(" delete from {$g5['auth_table']} where au_menu = '{$row['au_menu']}' ");
            continue;
        }

        $list = $i%2;
        ?>
        <tr>
            <td class="td_chk">
                <input type="hidden" name="au_menu[<?php echo $i ?>]" value="<?php echo $row['au_menu'] ?>">
                <input type="hidden" name="mb_id[<?php echo $i ?>]" value="<?php echo $row['mb_id'] ?>">
                <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo $row['mb_nick'] ?>님 권한</label>
                <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
            </td>
            <td class="td_mbid"><a href="?sfl=a.mb_id&amp;stx=<?php echo $row['mb_id'] ?>"><?php echo $row['mb_id'] ?></a></td>
            <td class="td_auth_mbnick"><?php echo $mb_nick ?></td>
            <td class="td_menu">
                <?php echo $row['au_menu'] ?>
                <?php echo $auth_menu[$row['au_menu']] ?>
            </td>
            <td class="td_auth"><?php echo $row['au_auth'] ?></td>
        </tr>
        <?php
    }

    if ($i==0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>

    <div class="btn_list">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value">
    </div>

    <?php
    $pagelist = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['PHP_SELF'].'?'.$qstr.'&amp;page=');
    echo $pagelist;
    ?>

    <?php
    //if (isset($stx))
    //    echo '<script>document.fsearch.sfl.value = "'.$sfl.'";</script>'."\n";

    if (strstr($sfl, 'mb_id'))
        $mb_id = $stx;
    else
        $mb_id = '';
    ?>
    </form>
</section>

<form name="fauthlist2" id="fauthlist2" action="./auth_update.php" method="post" autocomplete="off">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="<?php echo $token ?>">

<section id="add_admin" class="cbox">
    <h2>관리권한 추가</h2>
    <p>다음 양식에서 회원에게 관리권한을 부여하실 수 있습니다.</p>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="mb_id">회원아이디<strong class="sound_only">필수</strong></label></th>
        <td>
            <strong id="msg_mb_id" class="msg_sound_only"></strong>
            <input type="text" name="mb_id" value="<?php echo $mb_id ?>" id="mb_id" title="회원아이디" required class="required frm_input">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="au_menu">접근가능메뉴<strong class="sound_only">필수</strong></label></th>
        <td>
            <select id="au_menu" name="au_menu" required class="required" title="접근가능메뉴">
                <option value=''>선택하세요</option>
                <?php
                foreach($auth_menu as $key=>$value)
                {
                    if (!(substr($key, -3) == '000' || $key == '-' || !$key))
                        echo '<option value="'.$key.'">'.$key.' '.$value.'</option>';
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row">권한지정</th>
        <td>
            <input type="checkbox" name="r" value="r" id="r" checked>
            <label for="r">r (읽기)</label>
            <input type="checkbox" name="w" value="w" id="w">
            <label for="w">w (쓰기)</label>
            <input type="checkbox" name="d" value="d" id="d">
            <label for="d">d (삭제)</label>
        </td>
    </tr>
    </tbody>
    </table>

    <div class="btn_confirm">
        <input type="submit" value="완료" class="btn_submit">
    </div>
</section>

</form>

<script>
function fauthlist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
include_once ('./admin.tail.php');
?>