var table;
$('document').ready(function () {

    table = $('#listaArquivos').DataTable({
        "ajax": "../../../controller/arquivo/ControllerArquivo.php",
        "language": {
            "sEmptyTable": "Nenhum registro cadastrado",
            "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
            "sInfoFiltered": "(Filtrados de _MAX_ registros)",
            "sInfoPostFix": "",
            "sInfoThousands": ".",
            "sLengthMenu": "_MENU_ resultados por página",
            "sLoadingRecords": "Carregando...",
            "sProcessing": "Processando...",
            "sZeroRecords": "Nenhum arquivo encontrado",
            "sSearch": "Pesquisar",
            "paginate": {
                "previous": "Anterior",
                "next": "Próximo",
                "sFirst": "Primeiro",
                "sLast": "Último"
            },
            "oAria": {
                "sSortAscending": ": Ordenar colunas de forma ascendente",
                "sSortDescending": ": Ordenar colunas de forma descendente"
            }

        }
    });

    $(".modal").modal({ backdrop: false, keyboard: false, show: false });

    $(document).on('click', '#addArquivo', function () {
        $("#form")[0].reset();
        $("#modal_file").modal("show");
        $("#modal_file .modal-title").text("Adicionar Arquivo");
        resetFiels();
    });

    $(document).on("submit", "#form", function (event) {
        event.preventDefault();

        let id = $('[name="id"]').val();
        var dataForm = new FormData(this);
        let acao="add";

        if(id != ""){
            acao="update";
        }

        let url = "../../../controller/arquivo/ControllerArquivo.php?acao=" + acao;

        $.ajax({
            url: url,
            type: "POST",
            data: dataForm,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (data) {
                $("#modal-mensagem").modal("show");
                $("#modal-mensagem .modal-title").css("display", "none");
                if (data.erro) {
                    $("#modal-mensagem p").text(data.msg).attr("class", "alert alert-danger");
                    setTimeout(function () {
                        $("#modal-mensagem").modal("hide");
                    }, 3000);
                } else {

                    $("#modal-mensagem p").text(data.msg).attr("class", "alert alert-success");
                    setTimeout(function () {
                        $("#modal-mensagem").modal("hide");
                        $("#modal_file").modal("hide");
                        table.ajax.reload(null, false);
                    }, 3000);
                }
                $("#modal-mensagem .modal-footer").css("display", "none");

            },
            error: function (xhr, testStatus, error) {
                console.log(JSON.stringify(xhr));
            }
        });

    });

    $(document).on("click", "button.actionBtn", function (event) {

        event.preventDefault();
        var id = $(this).data("id");
        var action = $(this).data("action");
        var file = $(this).data("file");
        var version = $(this).data("version");

        if (action == "get") {

            $("#form")[0].reset();
            $("#modal_file").modal("show");
            $("#modal_file .modal-title").text("Atualizar Arquivo");
            resetFiels();
            $.ajax({

                url: "../../../controller/arquivo/ControllerArquivo.php?acao=get&id=" + id,
                type: "GET",
                dataType: "json",
                success: function (data) {

                    if (data.erro) {
                        $("#modal-mensagem").modal("show");
                        $("#modal-mensagem .modal-title").css("display", "none");
                        $("#modal-mensagem p").text(data.msg).attr("class", "alert alert-danger");
                        $("#modal-mensagem .modal-footer").css("display", "none");
                        setTimeout(function () {
                            $("#modal-mensagem").modal("hide");
                        }, 3000);
                    } else {
                        $('[name="id"]').val(data.id);
                        $('[name="version"]').val(data.version);
                        $('[name="name"]').val(data.name);
                        $('[name="file_name"]').val(data.file_name);
                    }
                },
                error: function (xhr, textStatus, error) {
                    console.log(JSON.stringify(xhr));
                }

            });
        }
        else if (action == "del") {
            if (confirm('Deseja realmente excluir?')) {
                $.ajax({
                    url: "../../../controller/arquivo/ControllerArquivo.php?acao=del&id="+id+"&version="+version+"&file="+file,
                    type: "POST",
                    dataType: "json",
                    success: function (data) {

                        $("#modal-mensagem").modal("show");
                        $("#modal-mensagem .modal-title").css("display", "none");
                        if (data.erro) {
                            $("#modal-mensagem p").text(data.msg).attr("class", "alert alert-danger");
                        } else {
                            table.ajax.reload();
                            $("#modal-mensagem p").text(data.msg).attr("class", "alert alert-success");
                        }
                        $("#modal-mensagem .modal-footer").css("display", "none");
                        setTimeout(function () {
                            $("#modal-mensagem").modal("hide");
                        }, 3000);
                    },
                    error: function (xhr, textStatus, error) {
                        console.log(JSON.stringify(xhr));
                    }

                });
            }
        }

    });

    $(document).on("click", ".forms", function (event) {
        event.preventDefault();
        var action = $(this).data("action");
        let dataForm = $("form").serializeArray();
        let acao=action;

        let url = "../../processos/login_process.php?acao=" + acao;

        $.ajax({
            url: url,
            type: "POST",
            data: dataForm,
            dataType: "json",
            success: function (data) {
                $("#modal-mensagem").modal("show");
                $("#modal-mensagem .modal-title").css("display", "none");
                if (data.erro) {
                    $("#modal-mensagem p").text(data.msg).attr("class", "alert alert-danger");
                    setTimeout(function () {
                        $("#modal-mensagem").modal("hide");
                    }, 3000);
                } else {

                    $("#modal-mensagem p").text(data.msg).attr("class", "alert alert-success");
                    setTimeout(function () {
                        $("#modal-mensagem").modal("hide");
                        location.replace("/");
                    }, 3000);
                }
                $("#modal-mensagem .modal-footer").css("display", "none");
                $("form")[0].reset();

            },
            error: function (xhr, testStatus, error) {
                console.log(JSON.stringify(xhr));
            }
        });

    });

    function resetFiels(){
        $('[name="id"]').val("");
        $('[name="version"]').val("");
        $('[name="name"]').val("");
        $('[name="file_name"]').val("");
    }

});
