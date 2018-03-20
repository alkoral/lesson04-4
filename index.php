<?php

$host = '127.0.0.1';
$dbname = 'korzun';
$user = 'korzun';
$pass = 'neto1653';
/*
$host = '127.0.0.1';
$dbname = 'lesson04-4';
$user = 'root';
$pass = '';
*/

try {
  $db = new PDO("mysql:host=$host; dbname=$dbname; charset=utf8", $user, $pass);
// Автоматом создаем новую таблицу
  $sql1 = "
  CREATE TABLE `Test_Table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  ";
  echo $db->prepare($sql1)->execute() ? 'Таблица <b>Test_Table</b> создана.<h4>Пожалуйста, для добавления, удаления и/или изменения полей используйте именно эту таблицу</h4>' : 'Таблица <b>Test_Table</b> уже существует.<h4>Пожалуйста, для добавления, удаления и/или изменения полей используйте именно эту таблицу</h4>';
  echo '<br>';

} catch (Exception $e) {
  die('Error: ' . $e->getMessage() . '<br>');
}

function get_param($param_name) {
  if (isset($_REQUEST[$param_name]) and !empty($_REQUEST[$param_name])) {
    return strip_tags(trim($_REQUEST[$param_name]));
  }
  else {
    return "";
  }
}

$action=get_param('action');
$field=get_param('field');

  if ($action=='delete' and !empty($field)) {
    $sql = "ALTER TABLE ".$_REQUEST['table_name']." DROP COLUMN ".$field."";
    $result = $db->prepare($sql)->execute();
    header ('location: index.php?table_name='.$_REQUEST['table_name'].'&action=view_details');
  }

  if (isset($_POST['update']) and !empty($field)) {
    $sql = "ALTER TABLE ".$_REQUEST['table_name']." CHANGE ".$field." ".$_REQUEST['field_update']." ".$_REQUEST['type_new']."(".$_REQUEST['length'].") NOT NULL";
    $result = $db->prepare($sql)->execute();
    header ('location: index.php?table_name='.$_REQUEST['table_name'].'&action=view_details');
  }

  if (isset($_POST['add'])) {
    $sql = "ALTER TABLE ".$_REQUEST['table_name']." 
    ADD ".$_REQUEST['new_field']." ".$_REQUEST['type_new']."(".$_REQUEST['length'].") NOT NULL 
    AFTER ".$_REQUEST['after']."";
    $result = $db->prepare($sql)->execute();
    header ('location: index.php?table_name='.$_REQUEST['table_name'].'&action=view_details');
  }
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Домашнее задание к лекции 4.4 «Управление таблицами и базами данных»</title>

<style>
    table { 
      border-spacing: 0;
      border-collapse: collapse;
    }

    table td, table th {
      border: 1px solid #ccc;
      padding: 5px;
    }
      
    table th {
      background: #eee;
    }
</style>

</head>
<body>

<h3>Все таблицы в базе данных «<?php echo $dbname; ?>»:</h3>
<form method="POST">
<?php
$sql = "SHOW TABLES";   // Выводим список всех таблиц в БД
$result = $db->query($sql);
  foreach($result as $row) {
    $table_name = $row[0];
    echo "<input type='radio' name='table_name' value='".$table_name."'>".$table_name."<br>\n";
}
?>
<br>
<input type='hidden' name='action' value='view_details'>
<input type='submit' name='details' value='Подробнее'></center>
</form>

<?php
if ($action=='view_details' and isset($_REQUEST['table_name'])) {   // Выводим структуру выбранной таблицы
  $statement = $db->query('DESCRIBE '.$_REQUEST['table_name'].'');
  $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    echo"
<h3>Структура таблицы ".$_REQUEST['table_name']."</h3>
<table>
  <tr>
    <th>Field</th>
    <th>Type</th>
    <th>Null</th>
    <th>Key</th>
    <th>Default</th>
    <th>Extra</th>
    <th>Управление</th>
  </tr>";
      foreach($result as $column){
    echo
    "<tr>
    <td><b>".$column['Field']."</b></td>
    <td>".$column['Type']."</td>
    <td>".$column['Null']."</td>
    <td>".$column['Key']."</td>
    <td>".$column['Default']."</td>
    <td>".$column['Extra']."</td>";
      if ($column['Field']=='id') {
        echo "<td bgcolor='#F6CECE'>Для ключевого поля изменения невозможны</td>";
        }
      else {
        $field=$column['Field'];
        $type=$column['Type'];
        echo
    "<td>
    <a href='?field=".$column['Field']."&table_name=".$_REQUEST['table_name']."&action=delete'>Удалить поле</a> | 
    <a href='?field=".$column['Field']."&table_name=".$_REQUEST['table_name']."&action=update'>Изменить название и/или тип поля</a></td>
    </tr>";
  }
}
    echo "</table>
    <h3><a href='?table_name=".$_REQUEST['table_name']."&action=add'>Добавить поле в эту таблицу</a></h3>";

}

if ($action=='update' and !empty($field)) { // Выводим форму для внесения изменений в поле
  echo "<h3>Измените название и/или тип поля <u>".$field."</u> в таблице <u>".$_REQUEST['table_name']."</u></h3>
<p>(Должны быть заполнены все поля)</p>
  <form method='POST'>
  Поле: <input type='text' name='field_update' placeholder='$field' value='$field'> &nbsp;
  <label for='type'>Выберите тип:</label>
    <select name='type_new'>
      <option selected='int' value='INT'>INT</option>
      <option value='varchar'>VARCHAR</option>
      <option value='text'>TEXT</option>
    </select>
   <input type='text' name='length' placeholder='Length/Values' size='15' maxlength='20' value=''> &nbsp; 
   <input type='submit' name='update' value='Обновить'>
    </form>";
}

if ($action=='add') {   // Выводим форму для добавления поля в таблицу
echo "
<h3>Добавить поле в таблицу <u>".$_REQUEST['table_name']."</u></h3>
<p>(Должны быть заполнены все поля)</p>
  <form method ='POST'>
  <input type='text' name='new_field' placeholder='Название поля' value=''>
  <label for='type'>Выберите тип:</label>
    <select name='type_new'>
      <option value='INT'>INT</option>
      <option value='varchar'>VARCHAR</option>
      <option value='text'>TEXT</option>
    </select>
   <input type='text' name='length' placeholder='Length/Values' size='15' maxlength='20' value=''> &nbsp; 
   Вставить после поля: 
    <select name='after'>";
    $statement = $db->query('DESCRIBE '.$_REQUEST['table_name'].'');
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach($result as $column){
      echo "
    <option value=".$column['Field'].">".$column['Field']."</option>";
      }
    echo "
    </select>
   <input type='submit' name='add' value='Добавить'>
  </form>";
}
?>
</body>
</html>