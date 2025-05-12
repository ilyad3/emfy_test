<?php
/*!
\file
\brief Класс для работы с базой данных

# Подключение класса
```php
// Подключение файла соединения с БД
include_once 'db.class.php';

// хост БД
define('db_host','localhost');

// Имя БД
define('db_name','name');

// Пользователь БД
define('db_user','root');

// Пароль БД
define('db_pass','password');

// Обявление класса для подключения к бд
$db = new DB_class(db_host,db_name,db_user,db_pass);
```

# Запросы к базе данных
## Вывод данных из базы
```php
$query = $db->select(true,"*","bdname","example='".$example."'");
```
В данном запросе передаётся 4 параметра:

1. Нужен ли циклический вывод
2. Что именно нужно достать из базы данных
3. Название базы данных
4. Ключевое слово (WHERE)

> Циклический вывод:
Если первый параметр передан, как *TRUE*, то вывод происходит следующим образом:
```php
print_r($query[0]['название колонки']);
print_r($query[1]['название колонки']);
print_r($query[2]['название колонки']);
```
Если Этот параметр передан, как *FALSE*, то вывод происходит вот так:
```php
print_r($query['название колонки']);
```

## Обновление данных базы
```php
$db->update('bdname',"`text`='$text',`step`='$step'","example='$example'");
```
В данном запросе передаётся 3 параметра:

1. Название базы данных
2. Что нужно изменить и на что
3. Ключевое слово (WHERE)

## Добавление данных в базу
```php
$db->insert('`dbname`',"`text`, `step`","$text,'$step'");
```
В данном запросе передаётся 3 параметра:

1. Название базы данных
2. Поля внутри базы данных в которые нужно вставить данные
3. Данные для вставки в эти поля

## Удаление данных из базы
```php
$db->delete('`dbname`',"example='$example'");
```
В данном запросе передаётся 2 параметра:

1. Название базы данных
2. Ключевое слово (WHERE)

 */
class DB_class {
	private $db_host, $db_name, $db_user, $db_pass, $db;
	/**
	 * Конструктор класса
	 *
	 * @param      string  $db_host  хост БД
	 * @param      string  $db_name  Имя ДБ
	 * @param      string  $db_user  Пользователь БД
	 * @param      string  $db_pass  Пароль БД
	 *
	 * @return     bool    Возврат маркера подключения
	 */
	function __construct($db_host, $db_name, $db_user, $db_pass) {
		if (!$this->db) {
			$con = @new mysqli($db_host, $db_user, $db_pass, $db_name);
			if (!$con->connect_error) {
				$this->db = true;
				$con->set_charset("utf8");
				$this->con = $con;
				return true;
			} else {
				return false; /// Сообщение о невозможности коннекта
			}
		}
	}

	/**
	 * Поиск в базе
	 *
	 * @param      bool    $while   Переключатель вовзарат(true - многомерный масив, false - одномерный)
	 * @param      string  $select  Что доставать
	 * @param      string  $from    Откуда доставать
	 * @param      string  $where   При каком условии
	 * @param      string  $order   Отсортировать по
	 * @param      bool    $debug   Режим отладки с выводом данных о запросе
	 * @param      string  $type    (Остатки от прошлых версий класса)
	 *
	 * @return     array     Возврат результата запроса
	 */
	function select($while, $select, $from, $where = null, $order = null, $debug = false, $type = null) {
		if ($where != NULL) {
			$where = "WHERE " . $where;
		}
		if ($order != NULL) {
			$order = "ORDER BY " . $order . " DESC";
		}
		$sql = "SELECT " . $select . " FROM " . $from . " " . $where . " " . $order . "";
		if ($debug == true) {
			print_r($sql);
		}
		$u_query = $this->con->query($sql);
		if ($u_query->num_rows != 0) {
			$count_row = 0;
			while ($query_row = $u_query->fetch_array(MYSQLI_ASSOC)) {
				$select_count = 0;
				$count_select = count($query_row);
				$select_array = NULL;
				while ($select_count < $count_select) {
					if ($while == true) {
						$return_array[$count_row] = $query_row;
						$select_count++;
					} else {
						$return_array = $query_row;
						$select_count++;
					}
				}
				$count_row++;
			}
		} else {
			$return_array = 0;
		}
		return $return_array;
	}

	/**
	 * Обновление в базе
	 *
	 * @param      string  $from   Где обновлять
	 * @param      string  $set    Что обновлять
	 * @param      string  $where  При каком условии
	 * @param      bool    $debug  Режим отладки с выводом данных о запросе
	 */
	function update($from, $set, $where, $debug = false) {
		if ($where != NULL) {
			$where = "WHERE " . $where;
		}
		$update_sql = "UPDATE " . $from . " SET " . $set . " " . $where . "";
		if ($debug == true) {
			print_r($update_sql);
		}
		$update_query = $this->con->query($update_sql);
	}

	/**
	 * Удаление в базе
	 *
	 * @param      string  $from   Где удалять
	 * @param      string  $where  Прик аком суловии
	 * @param      bool    $debug  Режим отладки с выводом данных о запросе
	 */
	function delete($from, $where, $debug = false) {
		if ($where != NULL) {
			$where = "WHERE " . $where;
		}
		$delete_sql = "DELETE FROM " . $from . " " . $where . "";
		if ($debug == true) {
			print_r($delete_sql);
		}
		$delete_query = $this->con->query($delete_sql);
	}

	/**
	 * Вставка в базу
	 *
	 * @param      string  $from    Куда вставлять
	 * @param      string  $insert  Что вставлять
	 * @param      string  $values  Какие значения вставлять
	 * @param      bool    $debug   Режим отладки с выводом данных о запросе
	 */
	function insert($from, $insert, $values, $debug = false) {
		$insert_sql = "INSERT INTO " . $from . " (" . $insert . ") VALUES (" . $values . ")";
		if ($debug == true) {
			print_r($insert_sql);
		}
		$insert_query = $this->con->query($insert_sql);
	}

	/**
	 * Очистка строки от специальных символов при помощи их экранирования
	 *
	 * @param      string  $value  Строка для очистки
	 *
	 * @return     string Очищенная строка
	 */
	function escape_string($value) {
		$value = $this->con->real_escape_string($value);
		return $value;
	}

	function __destruct() {
		mysqli_close($this->con);
		$this->db = false;
	}
}
?>