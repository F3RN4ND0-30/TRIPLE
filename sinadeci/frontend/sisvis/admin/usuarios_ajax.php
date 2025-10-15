// Guardar o editar usuario
$("#formUsuario").on("submit", function(e){
    e.preventDefault();

    let accion = $("#idUsuario").val() ? 'editar' : 'nuevo';
    let password = accion === 'nuevo' ? $("#password").val() : '';

    let formData = {
        accion: accion,
        idUsuario: $("#idUsuario").val(),
        numDoc: $("#numDoc").val(),
        nombres: $("#nombres").val(),
        apePat: $("#apePat").val(),
        apeMat: $("#apeMat").val(),
        usuario: $("#usuario").val(),
        estado: $("#estado").val(),
        tipoUsuario: $("#tipoUsuario").val(),
        password: password
    };

    $.ajax({
        url: "usuarios_guardar.php",
        type: "POST",
        data: formData,
        dataType: "json", // 👈 IMPORTANTE: para que no salga undefined
        success: function(response){
            if(response.success){
                $("#mensajeModal").html('<div class="alert alert-success">'+response.message+'</div>');
                setTimeout(() => location.reload(), 1000);
            } else {
                $("#mensajeModal").html('<div class="alert alert-danger">'+response.message+'</div>');
            }
        },
        error: function(xhr, status, error){
            console.error("Error AJAX:", status, error, xhr.responseText);
            $("#mensajeModal").html('<div class="alert alert-danger">Error al enviar datos.</div>');
        }
    });
});

// Cambiar contraseña
$("#formCambiarContrasena").on("submit", function(e){
    e.preventDefault();

    let formData = {
        accion: 'pass',
        idUsuario: $("#usuarioSelect").val(),
        nuevaContrasena: $("input[name='nuevaContrasena']").val()
    };

    $.ajax({
        url: "usuarios_guardar.php",
        type: "POST",
        data: formData,
        dataType: "json", // 👈 también aquí
        success: function(response){
            if(response.success){
                $("#mensajeContrasena").html('<div class="alert alert-success">'+response.message+'</div>');
                $("#formCambiarContrasena")[0].reset();
                setTimeout(() => $("#modalNuevaContrasena").modal("hide"), 1000);
            } else {
                $("#mensajeContrasena").html('<div class="alert alert-danger">'+response.message+'</div>');
            }
        },
        error: function(xhr, status, error){
            console.error("Error AJAX:", status, error, xhr.responseText);
            $("#mensajeContrasena").html('<div class="alert alert-danger">Error al enviar datos.</div>');
        }
    });
});
