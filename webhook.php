<?php
include "amo/amo.class.php";
include "db.class.php";
$file = file("amo/amo.txt");
$amo = new Amo(json_decode($file[0],true));

// хост БД
define('db_host', 'localhost');

// Имя БД
define('db_name', 'amocrm');

// Пользователь БД
define('db_user', 'root');

// Пароль БД
define('db_pass', 'Bkmz1527');

$db = new DB_class(db_host, db_name, db_user, db_pass);

$request = $_REQUEST;
$type = "";
$event_type = "";
$type_array = array(
    "leads" => "Сделка",
    "contacts" => "Контакт",
);
$event_type_array = array(
    "add" => "добавлен(-а)",
    "update" => "обновлён(-а)",
);
if (isset($request['leads'])) {
    $type = "leads";     
}elseif (isset($request['contacts'])) {
    $type = "contacts";
}else{
    exit('No type info');
}
if (isset($request[$type]['add'])) {
   $event_type = "add";     
}elseif (isset($request[$type]['update'])) {
    $event_type = "update";
}else{
    exit('No event_type info');
}
$responsible_user_id = $request[$type][$event_type][0]['responsible_user_id'];
$date_create = $request[$type][$event_type][0]['date_create'];
$name = $request[$type][$event_type][0]['name'];
$id = $request[$type][$event_type][0]['id'];
$custom_fields = $request[$type][$event_type][0]['custom_fields'];
$custom_fields_db = json_encode($custom_fields);
$user_info = $amo->GET_REQUEST("users","?limit=250");
$user_name = "";
for ($i=0;$i<count($user_info);$i++) {
    if ($user_info[$i]['id'] == $responsible_user_id) {
        $user_name = $user_info[$i]['name'];
    }
}
$note_text = $type_array[$type]." с именем ".$name." ".$event_type_array[$event_type]." ответственный: ".$user_name." время: ".date("d.m.Y в H:i:s",$date_create);
if ($event_type == "add") {
    $db->insert("`info`", "`field_amo_type`,`field_amo_id`,`fields_info`", "'$type','$id','$custom_fields_db'");
    
}elseif($event_type == "update") {
    $db_info = $db->select(false, "*", "info", "field_amo_id='$id'");
    if ($db_info != 0) {
        $db_custom = json_decode($db_info['fields_info'],true);
    }else{
        $db_custom = array();
    }
    $db_custom = json_decode($db_info['fields_info'],true);
    $diff_result = CheckDiff($db_custom, $custom_fields);
    $note_text = $note_text."\n".$diff_result;
    
    $custom_fields_db = json_encode($custom_fields);
    $db->update('`info`', "fields_info='$custom_fields_db'", "field_amo_id='$id'");
}
$note_array = $amo->ADD_FILED('entity_id', (int) $id, 'add');
$note_array = $amo->ADD_FILED('note_type', "common", 'add', $note_array);
$note_array = $amo->ADD_FILED('params', array('text' => $note_text), 'add', $note_array);
$note_id = $amo->POST_REQUEST($note_array, $type.'/notes');


function CheckDiff(array $oldArray, array $newArray): string
{
    $messages = [];

    $oldIndexed = [];
    foreach ($oldArray as $item) {
        $oldIndexed[$item['id']] = $item;
    }

    $newIndexed = [];
    foreach ($newArray as $item) {
        $newIndexed[$item['id']] = $item;
    }

    foreach ($oldIndexed as $id => $oldItem) {
        if (!isset($newIndexed[$id])) {
            continue;
        }

        $newItem = $newIndexed[$id];
        $name = $newItem['name'] ?? "ID {$id}";

        $oldValues = $oldItem['values'] ?? [];
        $newValues = $newItem['values'] ?? [];
        $max = max(count($oldValues), count($newValues));

        for ($i = 0; $i < $max; $i++) {
            $oldValue = $oldValues[$i]['value'] ?? null;
            $newValue = $newValues[$i]['value'] ?? null;

            if (!array_key_exists($i, $oldValues) && $newValue !== null) {
                $messages[] = "Добавлено новое значение у элемента '{$name}': '{$newValue}'";
            } elseif ($oldValue !== $newValue) {
                $messages[] = "Поле у элемента '{$name}' было изменено, новое значение: '{$newValue}'";
            }
        }
    }

    foreach ($newIndexed as $id => $newItem) {
        if (!isset($oldIndexed[$id])) {
            $name = $newItem['name'] ?? "ID {$id}";
            $values = $newItem['values'] ?? [];
            foreach ($values as $i => $val) {
                $valStr = $val['value'] ?? 'null';
                $messages[] = "Добавлено новое значение у элемента '{$name}': '{$valStr}'";
            }
        }
    }

    $messages = implode("\n",$messages);
    return $messages;
}




?>