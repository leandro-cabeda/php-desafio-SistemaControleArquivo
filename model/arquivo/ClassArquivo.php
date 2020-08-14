<?php

require_once('../../database/DB.php');

class Arquivos
{
    private $sql;
    private $res;
    private $db;

    public function __construct()
    {
        $this->db = new Conecta();
        $this->db = $this->db->getDb();
    }

    public function listaArquivos()
    {
        $this->sql = 'select * from files';
        $this->res = $this->db->query($this->sql);
        $data = array("data" => array());
        while ($row = $this->res->fetch_assoc()) {
            $id = $row['id'];
            $name = $row['name'];
            $file = $row["file"];
            $version = $row["version"];
            $ext = strrchr($file, '.');
            $botoes = "<button class='btn btn-primary actionBtn'  data-id='$id' data-action='get'><i class='fas fa-edit'></i></button>&nbsp;";
            $botoes .= "<button class='btn btn-danger actionBtn' data-file='$file' data-version='$version' data-id='$id' data-action='del'><i class='fas fa-trash-alt'></i></button>";
            $linha = [];
            $linha[] = $id;
            $linha[] = $name;
            if ($ext == ".pdf") {
                $linha[] = $file;
            } else {
                $linha[] = "<img src='../../../uploads/v" . $version . "_" . $file . "' alt='$name' class='img-thumbnail w-25'>";
            }
            $linha[] = $version;
            $linha[] = $botoes;
            $data['data'][] = $linha;
        }

        return json_encode($data);
    }

    public function addArquivo($data, $file)
    {
        $dataJson = array();

        if (trim($data['name']) != "" && $file['file']['name'] != "") {

            if (!$this->verificaNomeDuplicado($data['name'])) {

                $respFile = $this->saveArquivo($file, $data);
                $respArquivo = explode(" ", $respFile);

                if (!in_array("Erro!", $respArquivo)) {

                    $this->sql = 'insert into files (name,file,version)';
                    $this->sql .= " values ('" . $data['name'] . "','" . $respFile . "',1)";
                    $this->res = $this->db->query($this->sql);

                    if (!$this->res) {
                        $dataJson['erro'] = true;
                        $dataJson['msg'] = "Erro ao adicionar o arquivo no banco de dados!!";
                        return json_encode($dataJson);
                    } else {
                        $dataJson['erro'] = false;
                        $dataJson['msg'] = "Adicionado arquivo com sucesso no banco de dados!!!";
                        return json_encode($dataJson);
                    }
                } else {
                    $dataJson['erro'] = true;
                    $dataJson['msg'] = $respFile;
                    return json_encode($dataJson);
                }
            } else {
                $dataJson['erro'] = true;
                $dataJson['msg'] = "Erro ! Este arquivo já está cadastrado no banco!";
                return json_encode($dataJson);
            }
        } else {
            $dataJson['erro'] = true;
            $dataJson['msg'] = "Erro ! Os campos precisam ser preenchidos!";
            return json_encode($dataJson);
        }
    }


    public function updateArquivos($data, $file)
    {
        $dataJson = array();

        if (trim($data['name']) != "") {

            $this->sql = "update files set name= '" . $data['name'] . "'";

            if ($file['file']['name'] != "") {
                $respFile = $this->saveArquivo($file, $data);
                $respArquivo = explode(" ", $respFile);

                if (in_array("Erro!", $respArquivo)) {
                    $dataJson['erro'] = true;
                    $dataJson['msg'] = $respFile;
                    return json_encode($dataJson);
                }
                $version = (int)$data['version'];
                $arq = "../../uploads/v" . $version . "_" . $data['file_name'];
                unlink($arq);

                $cont = 1 + $version;
                $this->sql .= ", file='" . $respFile . "', version=" . $cont;
            }

            $this->sql .= " where id=" . $data['id'];
            $this->res = $this->db->query($this->sql);

            if (!$this->res) {
                $dataJson['erro'] = true;
                $dataJson['msg'] = "Erro ao adicionar o arquivo no banco de dados!!";
                return json_encode($dataJson);
            } else {
                $dataJson['erro'] = false;
                $dataJson['msg'] = "Atualizado arquivo com sucesso no banco de dados!!!";
                return json_encode($dataJson);
            }
        } else {
            $dataJson['erro'] = true;
            $dataJson['msg'] = "Erro ! O campo nome precisa ser preenchido!";
            return json_encode($dataJson);
        }
    }

    public function getArquivo($id)
    {
        $this->sql = "select * from files where id=" . $id;

        $this->res = $this->db->query($this->sql);
        $dataJson = array();
        if (!$this->res) {
            $dataJson['erro'] = true;
            $dataJson['msg'] = "Erro ! Arquivo não encontrado do id:" . $id . " !";
            return json_encode($dataJson);
        } else {
            while ($row = $this->res->fetch_assoc()) {
                $dataJson['id'] = $row['id'];
                $dataJson['name'] = $row["name"];
                $dataJson['version'] = $row['version'];
                $dataJson['file_name'] = $row['file'];
            }

            return json_encode($dataJson);
        }
    }


    public function delArquivo($id, $version, $file)
    {
        $dataJson = array();
        $this->sql = "delete from files where id=$id";
        $this->res = $this->db->query($this->sql);

        if (!$this->res) {
            $dataJson['erro'] = true;
            $dataJson['msg'] = "Erro ao deletar arquivo do id: " . $id . " !";
            return json_encode($dataJson);
        } else {

            unlink("../../uploads/v" . $version . "_" . $file);
            $dataJson['erro'] = false;
            $dataJson['msg'] = "Deletado arquivo com sucesso do id: " . $id . " !";
            return json_encode($dataJson);
        }
    }


    private function verificaNomeDuplicado($name)
    {
        $this->sql = "select * from files where upper(name)=upper('$name')";
        $this->res = $this->db->query($this->sql);


        if ($this->res->num_rows > 0) {
            return true;
        }

        return false;
    }

    private function saveArquivo($file, $data)
    {
        $arquivoTemp = $file['file']['tmp_name'];
        $arquivoOrigem = $file['file']['name'];
        $extensao = array('.pdf', '.jpg', '.png', '.gif', '.bmp');
        $ext_arquivo = strrchr($arquivoOrigem, '.');
        $arquivoDestino = "";

        if (!in_array(strtolower($ext_arquivo), $extensao)) {
            return "Erro! extensão deste tipo de arquivo não é permitido";
        }

        $pasta = "../../uploads/";

        if (!file_exists($pasta)) {
            mkdir($pasta, 0777, true);
        }

        if ($data['id'] != "") {
            $cont = 1 + (int)$data['version'];
            $arquivoDestino = $pasta . "v" . $cont . "_" . $arquivoOrigem;

            if (move_uploaded_file($arquivoTemp, $arquivoDestino)) {
                return $arquivoOrigem;
            }
            return "Erro! ocorreu falha ao salvar o arquivo";
        } else {
            $arquivoDestino = $pasta . "v1_" . $arquivoOrigem;

            if (move_uploaded_file($arquivoTemp, $arquivoDestino)) {
                return  $arquivoOrigem;
            }
            return "Erro! ocorreu falha ao salvar o arquivo";
        }
    }
}
