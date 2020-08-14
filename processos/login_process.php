<?php

require_once('../database/DB.php');
$conect = new Conecta();
$db = $conect->getDb();

if (!isset($_SESSION)) {
    session_start();
}

if (isset($_REQUEST["acao"])) {
    $acao = $_REQUEST["acao"];
}

switch ($acao) {
    case "login":
        echo loginUsuario($_REQUEST, $db);
        break;
    case "register":
        echo registerUsuario($_REQUEST, $db);
        break;
}


function loginUsuario($user, $db)
{
    $data = array();

    if (trim($user['email']) != "" && trim($user['password']) != "") {
        $sql = "select * from users where upper(email)=upper('" . $user['email'] . "')";
        $res = $db->query($sql);

        if ($res->num_rows > 0) {

            $sql2 = "select * from users where upper(email)=upper('" . $user['email'] . "')";
            $sql2 .= " and password='" . md5($user['password']) . "'";
            $res2 = $db->query($sql2);

            if ($res2->num_rows > 0) {

                while ($row = $res->fetch_assoc()) {
                    $_SESSION['id'] = $row['id'];
                    $_SESSION['email'] = $row["email"];
                    $data['msg'] = "Usuário autenticado com sucesso!";
                    $data['erro'] = false;
                }
                return json_encode($data);
            } else {
                $data['erro'] = true;
                $data['msg'] = "Erro ! Password do usuário incorreto, por favor verifique!";
                return json_encode($data);
            }
        } else {
            $data['erro'] = true;
            $data['msg'] = "Erro ! Usuário não cadastrado!";
            return json_encode($data);
        }
    } else {
        $data['erro'] = true;
        $data['msg'] = "Erro ! Os campos precisam ser preenchidos!";
        return json_encode($data);
    }
}


function registerUsuario($user, $db)
{
    $data = array();

    if (trim($user['email']) != "" && trim($user['password']) != "") {

        if (!verificaEmailDuplicado($user['email'], $db)) {

            $sql = "insert into users (email,password)";
            $sql .= " values ('" . $user['email'] . "','" . md5($user['password']) . "')";
            $res = $db->query($sql);

            if ($res) {
                return loginUsuario($user, $db);
            } else {
                $data['erro'] = true;
                $data['msg'] = "Erro ao adicionar o usuário no banco de dados!!";
                return json_encode($data);
            }
        } else {
            $data['erro'] = true;
            $data['msg'] = "Erro ! Este usuário já está cadastrado no banco!";
            return json_encode($data);
        }
    } else {
        $data['erro'] = true;
        $data['msg'] = "Erro ! Os campos precisam ser preenchidos!";
        return json_encode($data);
    }
}

function verificaEmailDuplicado($email, $db)
{
    $sql = "select * from users where email='$email'";
    $res = $db->query($sql);

    if ($res->num_rows > 0) {
        return true;
    }

    return false;
}
