<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Model_Mancard_People — операции с сотрудниками (пиплами).
 *
 * Сотрудники организации, карточка сотрудника (CRUD), массовое перемещение,
 * поиск по ФИО и по идентификатору, карты (идентификаторы) и категории доступа сотрудника.
 *
 * Организации — Model_Mancard_Org.
 */
class Model_Mancard_People extends Model {

    /**
     * Преобразование строки БД (windows-1251) в UTF-8
     */
    protected function _utf($value)
    {
        return iconv('windows-1251', 'UTF-8', $value);
    }

    /**
     * Преобразование строки UTF-8 в кодировку БД (windows-1251)
     */
    protected function _win($value)
    {
        return iconv('UTF-8', 'windows-1251', $value);
    }

    /**
     * Получить новый ID сотрудника из генератора GEN_PEOPLE_ID
     *
     * @return int
     */
    private function getNewIdPep()
    {
        $sql = 'SELECT GEN_ID(GEN_PEOPLE_ID, 1) FROM RDB$DATABASE';

        $id = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->get('GEN_ID');

        return (int)$id;
    }

    /**
     * Получить сотрудников организации
     */
    public function getPeopleByOrganization($id_org)
    {
        $id_org = (int)$id_org;

        $sql = 'SELECT 
                    p.ID_PEP,
                    p.SURNAME,
                    p.NAME,
                    p.PATRONYMIC,
                    p.POST,
                    p.PHONEWORK,
                    p.PHONECELLULAR,
                    p."ACTIVE",
                    p.NOTE,
                    p.TABNUM,
                    p.LOGIN,
                    p.ID_ORG,
                    o.NAME AS ORG_NAME,
                    (SELECT MAX(ID_CARD) FROM CARD WHERE ID_PEP = p.ID_PEP) AS ID_CARD
                FROM PEOPLE p
                JOIN ORGANIZATION o ON o.ID_ORG = p.ID_ORG
                WHERE p.ID_ORG = ' . $id_org . '
                AND p.ID_ORG NOT IN (2, 3)
                ORDER BY p.SURNAME, p.NAME, p.PATRONYMIC';

        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();

        $result = array();
        foreach ($query as $row) {
            $result[] = array(
                'ID_PEP' => $row['ID_PEP'],
                'SURNAME' => $this->_utf($row['SURNAME']),
                'NAME' => $this->_utf($row['NAME']),
                'PATRONYMIC' => $this->_utf($row['PATRONYMIC']),
                'POST' => $this->_utf($row['POST']),
                'PHONEWORK' => $row['PHONEWORK'],
                'PHONECELLULAR' => $row['PHONECELLULAR'],
                'ACTIVE' => $row['ACTIVE'],
                'NOTE' => $this->_utf($row['NOTE']),
                'TABNUM' => $row['TABNUM'],
                'LOGIN' => $row['LOGIN'],
                'ID_ORG' => $row['ID_ORG'],
                'ORG_NAME' => $this->_utf($row['ORG_NAME']),
                'ID_CARD' => $row['ID_CARD'],
            );
        }

        return $result;
    }

    /**
     * Получить данные сотрудника
     */
    public function getPerson($id_pep)
    {
        $id_pep = (int)$id_pep;

        $sql = 'SELECT 
                    p.ID_PEP,
                    p.SURNAME,
                    p.NAME,
                    p.PATRONYMIC,
                    p.ID_ORG,
                    p.TABNUM,
                    p.LOGIN,
                    p.POST,
                    p.PHONEHOME,
                    p.PHONECELLULAR,
                    p.PHONEWORK,
                    p.DATEBIRTH,
                    p.PLACEBIRTH,
                    p.PLACELIFE,
                    p.PLACEREG,
                    p.NUMDOC,
                    p.DATEDOC,
                    p.PLACEDOC,
                    p."ACTIVE",
                    p.NOTE,
                    p.SYSNOTE,
                    o.NAME AS ORG_NAME
                FROM PEOPLE p
                JOIN ORGANIZATION o ON o.ID_ORG = p.ID_ORG
                WHERE p.ID_PEP = ' . $id_pep;

        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();

        if (empty($query)) {
            return null;
        }

        $row = $query[0];
        return array(
            'ID_PEP' => $row['ID_PEP'],
            'SURNAME' => $this->_utf($row['SURNAME']),
            'NAME' => $this->_utf($row['NAME']),
            'PATRONYMIC' => $this->_utf($row['PATRONYMIC']),
            'ID_ORG' => $row['ID_ORG'],
            'ORG_NAME' => $this->_utf($row['ORG_NAME']),
            'TABNUM' => $row['TABNUM'],
            'LOGIN' => $row['LOGIN'],
            'POST' => $this->_utf($row['POST']),
            'PHONEHOME' => $row['PHONEHOME'],
            'PHONECELLULAR' => $row['PHONECELLULAR'],
            'PHONEWORK' => $row['PHONEWORK'],
            'DATEBIRTH' => $row['DATEBIRTH'],
            'PLACEBIRTH' => $this->_utf($row['PLACEBIRTH']),
            'PLACELIFE' => $this->_utf($row['PLACELIFE']),
            'PLACEREG' => $this->_utf($row['PLACEREG']),
            'NUMDOC' => $row['NUMDOC'],
            'DATEDOC' => $row['DATEDOC'],
            'PLACEDOC' => $this->_utf($row['PLACEDOC']),
            'ACTIVE' => $row['ACTIVE'],
            'NOTE' => $this->_utf($row['NOTE']),
            'SYSNOTE' => $row['SYSNOTE'],
        );
    }

    /**
     * Добавить сотрудника
     */
    public function addPerson($data)
    {
        // Новый ID берём из генератора GEN_PEOPLE_ID
        $new_id = $this->getNewIdPep();

        // Экранируем данные
        $surname = $this->_win($data['surname']);
        $name = $this->_win($data['name']);
        $patronymic = $this->_win($data['patronymic']);
        $post = $this->_win($data['post']);
        $placebirth = $this->_win($data['placebirth']);
        $placelife = $this->_win($data['placelife']);
        $placereg = $this->_win($data['placereg']);
        $placedoc = $this->_win($data['placedoc']);
        $note = $this->_win($data['note']);
        $sysnote = $this->_win($data['sysnote']);

        $datebirth = !empty($data['datebirth']) ? "'" . $data['datebirth'] . "'" : 'NULL';
        $datedoc = !empty($data['datedoc']) ? "'" . $data['datedoc'] . "'" : 'NULL';

        $sql = 'INSERT INTO PEOPLE (
                    ID_PEP, ID_DB, ID_ORG, SURNAME, NAME, PATRONYMIC,
                    DATEBIRTH, PLACEBIRTH, PLACELIFE, PLACEREG,
                    PHONEHOME, PHONECELLULAR, PHONEWORK,
                    NUMDOC, DATEDOC, PLACEDOC,
                    "ACTIVE", FLAG, LOGIN, PSWD, POST, TABNUM, NOTE, SYSNOTE, TIME_STAMP
                ) VALUES (
                    ' . $new_id . ', 1, ' . (int)$data['id_org'] . ',
                    \'' . $surname . '\', \'' . $name . '\', \'' . $patronymic . '\',
                    ' . $datebirth . ', \'' . $placebirth . '\', \'' . $placelife . '\', \'' . $placereg . '\',
                    \'' . $data['phonehome'] . '\', \'' . $data['phonecellular'] . '\', \'' . $data['phonework'] . '\',
                    \'' . $data['numdoc'] . '\', ' . $datedoc . ', \'' . $placedoc . '\',
                    ' . (int)$data['active'] . ', 0, \'' . $data['login'] . '\', \'\', \'' . $post . '\',
                    \'' . $data['tabnum'] . '\', \'' . $note . '\', \'' . $sysnote . '\', CURRENT_TIMESTAMP
                )';

        DB::query(Database::INSERT, $sql)
            ->execute(Database::instance('fb'));

        return $new_id;
    }

    /**
     * Обновить данные сотрудника
     */
    public function updatePerson($id_pep, $data)
    {
        $id_pep = (int)$id_pep;

        $surname = $this->_win($data['surname']);
        $name = $this->_win($data['name']);
        $patronymic = $this->_win($data['patronymic']);
        $post = $this->_win($data['post']);
        $placebirth = $this->_win($data['placebirth']);
        $placelife = $this->_win($data['placelife']);
        $placereg = $this->_win($data['placereg']);
        $placedoc = $this->_win($data['placedoc']);
        $note = $this->_win($data['note']);
        $sysnote = $this->_win($data['sysnote']);

        $datebirth = !empty($data['datebirth']) ? "'" . $data['datebirth'] . "'" : 'NULL';
        $datedoc = !empty($data['datedoc']) ? "'" . $data['datedoc'] . "'" : 'NULL';

        $sql = 'UPDATE PEOPLE SET
                    ID_ORG = ' . (int)$data['id_org'] . ',
                    SURNAME = \'' . $surname . '\',
                    NAME = \'' . $name . '\',
                    PATRONYMIC = \'' . $patronymic . '\',
                    DATEBIRTH = ' . $datebirth . ',
                    PLACEBIRTH = \'' . $placebirth . '\',
                    PLACELIFE = \'' . $placelife . '\',
                    PLACEREG = \'' . $placereg . '\',
                    PHONEHOME = \'' . $data['phonehome'] . '\',
                    PHONECELLULAR = \'' . $data['phonecellular'] . '\',
                    PHONEWORK = \'' . $data['phonework'] . '\',
                    NUMDOC = \'' . $data['numdoc'] . '\',
                    DATEDOC = ' . $datedoc . ',
                    PLACEDOC = \'' . $placedoc . '\',
                    "ACTIVE" = ' . (int)$data['active'] . ',
                    POST = \'' . $post . '\',
                    TABNUM = \'' . $data['tabnum'] . '\',
                    LOGIN = \'' . $data['login'] . '\',
                    NOTE = \'' . $note . '\',
                    SYSNOTE = \'' . $sysnote . '\',
                    TIME_STAMP = CURRENT_TIMESTAMP
                WHERE ID_PEP = ' . $id_pep;

        DB::query(Database::UPDATE, $sql)
            ->execute(Database::instance('fb'));
    }

    /**
     * Удалить сотрудника
     */
    public function deletePerson($id_pep)
    {
        $id_pep = (int)$id_pep;

        // Удаляем карты
        $sql = 'DELETE FROM CARD WHERE ID_PEP = ' . $id_pep;
        DB::query(Database::DELETE, $sql)
            ->execute(Database::instance('fb'));

        // Удаляем сотрудника
        $sql = 'DELETE FROM PEOPLE WHERE ID_PEP = ' . $id_pep . ' AND ID_PEP != 1';
        DB::query(Database::DELETE, $sql)
            ->execute(Database::instance('fb'));
    }

    /**
     * Массовое перемещение сотрудников
     */
    public function movePeople($person_ids, $target_org_id)
    {
        if (empty($person_ids)) {
            return 0;
        }

        $ids_str = implode(',', $person_ids);
        $target_org_id = (int)$target_org_id;

        $sql = 'UPDATE PEOPLE 
                SET ID_ORG = ' . $target_org_id . ', TIME_STAMP = CURRENT_TIMESTAMP 
                WHERE ID_PEP IN (' . $ids_str . ')';

        $result = DB::query(Database::UPDATE, $sql)
            ->execute(Database::instance('fb'));

        return count($person_ids);
    }

    /**
     * Получить всех сотрудников с информацией об организации
     */
    public function getAllPeopleWithOrgs()
    {
        $sql = 'SELECT 
                    p.ID_PEP,
                    p.SURNAME,
                    p.NAME,
                    p.PATRONYMIC,
                    p.ID_ORG,
                    o.NAME AS ORG_NAME,
                    p."ACTIVE",
                    p.POST
                FROM PEOPLE p
                JOIN ORGANIZATION o ON o.ID_ORG = p.ID_ORG
                WHERE p.ID_ORG NOT IN (2, 3)
                ORDER BY o.NAME, p.SURNAME, p.NAME';

        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();

        $result = array();
        foreach ($query as $row) {
            $result[] = array(
                'ID_PEP' => $row['ID_PEP'],
                'SURNAME' => $this->_utf($row['SURNAME']),
                'NAME' => $this->_utf($row['NAME']),
                'PATRONYMIC' => $this->_utf($row['PATRONYMIC']),
                'ID_ORG' => $row['ID_ORG'],
                'ORG_NAME' => $this->_utf($row['ORG_NAME']),
                'ACTIVE' => $row['ACTIVE'],
                'POST' => $this->_utf($row['POST']),
            );
        }

        return $result;
    }

    /**
     * Поиск сотрудников по ФИО
     */
    public function searchPeople($query)
    {
        $query = trim($query);

        if (empty($query) || strlen($query) < 2) {
            return array();
        }

        // Экранируем кавычки для безопасности
        $query_safe = str_replace("'", "''", $query);

        $sql = 'SELECT 
                    p.ID_PEP,
                    p.SURNAME,
                    p.NAME,
                    p.PATRONYMIC,
                    p.ID_ORG,
                    o.NAME AS ORG_NAME
                FROM PEOPLE p
                JOIN ORGANIZATION o ON o.ID_ORG = p.ID_ORG
                WHERE p.ID_ORG NOT IN (2, 3)
                AND (
                    p.SURNAME CONTAINING \'' . $query_safe . '\' OR
                    p.NAME CONTAINING \'' . $query_safe . '\' OR
                    p.PATRONYMIC CONTAINING \'' . $query_safe . '\'
                )
                ORDER BY p.SURNAME, p.NAME';

        $query = DB::query(Database::SELECT, $this->_win($sql))
            ->execute(Database::instance('fb'))
            ->as_array();

        $result = array();
        foreach ($query as $row) {
            $result[] = array(
                'ID_PEP' => $row['ID_PEP'],
                'SURNAME' => $this->_utf($row['SURNAME']),
                'NAME' => $this->_utf($row['NAME']),
                'PATRONYMIC' => $this->_utf($row['PATRONYMIC']),
                'ID_ORG' => $row['ID_ORG'],
                'ORG_NAME' => $this->_utf($row['ORG_NAME']),
            );
        }

        return $result;
    }

    /**
     * Поиск сотрудников по номеру карты (идентификатору)
     */
    public function searchByCard($query)
    {
        $query = trim($query);

        if (empty($query) || strlen($query) < 2) {
            return array();
        }

        // Экранируем кавычки для безопасности
        $query_safe = str_replace("'", "''", $query);

        $sql = 'SELECT 
                    p.ID_PEP,
                    p.SURNAME,
                    p.NAME,
                    p.PATRONYMIC,
                    p.ID_ORG,
                    o.NAME AS ORG_NAME,
                    c.ID_CARD,
                    ct.NAME AS CARDTYPE_NAME
                FROM PEOPLE p
                JOIN ORGANIZATION o ON o.ID_ORG = p.ID_ORG
                JOIN CARD c ON c.ID_PEP = p.ID_PEP
                JOIN CARDTYPE ct ON ct.ID = c.ID_CARDTYPE
                WHERE p.ID_ORG NOT IN (2, 3)
                AND (
                    c.ID_CARD CONTAINING \'' . $query_safe . '\'
                )
                ORDER BY p.SURNAME, p.NAME';

        $query = DB::query(Database::SELECT, $this->_win($sql))
            ->execute(Database::instance('fb'))
            ->as_array();

        $result = array();
        foreach ($query as $row) {
            $result[] = array(
                'ID_PEP' => $row['ID_PEP'],
                'SURNAME' => $this->_utf($row['SURNAME']),
                'NAME' => $this->_utf($row['NAME']),
                'PATRONYMIC' => $this->_utf($row['PATRONYMIC']),
                'ID_ORG' => $row['ID_ORG'],
                'ORG_NAME' => $this->_utf($row['ORG_NAME']),
                'ID_CARD' => $row['ID_CARD'],
                'CARDTYPE_NAME' => $this->_utf($row['CARDTYPE_NAME']),
            );
        }

        return $result;
    }

    /**
     * Получить карты (идентификаторы) сотрудника
     */
    public function getPersonCards($id_pep)
    {
        $id_pep = (int)$id_pep;

        $sql = 'SELECT 
                    c.ID_CARD,
                    c.ID_CARDTYPE,
                    ct.NAME AS CARDTYPE_NAME,
                    ct.SMALLNAME AS CARDTYPE_SMALLNAME,
                    c.TIMESTART,
                    c.TIMEEND,
                    c."ACTIVE",
                    c.NOTE
                FROM CARD c
                JOIN CARDTYPE ct ON ct.ID = c.ID_CARDTYPE
                WHERE c.ID_PEP = ' . $id_pep . '
                AND c.ID_DB = 1
                ORDER BY c.ID_CARDTYPE, c.ID_CARD';

        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();

        $result = array();
        foreach ($query as $row) {
            $result[] = array(
                'ID_CARD' => $row['ID_CARD'],
                'ID_CARDTYPE' => $row['ID_CARDTYPE'],
                'CARDTYPE_NAME' => $this->_utf($row['CARDTYPE_NAME']),
                'CARDTYPE_SMALLNAME' => $this->_utf($row['CARDTYPE_SMALLNAME']),
                'TIMESTART' => $row['TIMESTART'],
                'TIMEEND' => $row['TIMEEND'],
                'ACTIVE' => $row['ACTIVE'],
                'NOTE' => $this->_utf($row['NOTE']),
            );
        }

        return $result;
    }

    /**
     * Получить категории доступа сотрудника
     */
    public function getPersonAccessNames($id_pep)
    {
        $id_pep = (int)$id_pep;

        $sql = 'SELECT ID_ACCESSNAME 
                FROM SS_ACCESSUSER 
                WHERE ID_PEP = ' . $id_pep;

        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();

        $result = array();
        foreach ($query as $row) {
            $result[] = $row['ID_ACCESSNAME'];
        }

        return $result;
    }

    /**
     * Получить организацию сотрудника
     */
    public function getPersonOrganization($id_pep)
    {
        $id_pep = (int)$id_pep;

        $sql = 'SELECT ID_ORG FROM PEOPLE WHERE ID_PEP = ' . $id_pep;
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();

        if (empty($query)) {
            return null;
        }

        return $query[0]['ID_ORG'];
    }

    /**
     * Обновить категории доступа для сотрудника
     */
    public function updatePersonAccessNames($id_pep, $access_ids)
    {
        $id_pep = (int)$id_pep;

        // Удаляем старые
        $sql = 'DELETE FROM SS_ACCESSUSER WHERE ID_PEP = ' . $id_pep;
        DB::query(Database::DELETE, $sql)
            ->execute(Database::instance('fb'));

        // Добавляем новые
        if (!empty($access_ids)) {
            $values = array();
            foreach ($access_ids as $access_id) {
                $access_id = (int)$access_id;
                $values[] = '(GEN_ID(GEN_SS_ACCESSUSER, 1), 1, ' . $id_pep . ', ' . $access_id . ')';
            }

            $sql = 'INSERT INTO SS_ACCESSUSER (ID_ACCESSUSER, ID_DB, ID_PEP, ID_ACCESSNAME) VALUES ' . implode(',', $values);
            DB::query(Database::INSERT, $sql)
                ->execute(Database::instance('fb'));
        }
    }

}
