function redirectWhatsapWeb() {
    let phoneNumber = '56933324037'; // Asegúrate de incluir el código de país correcto.
    let whatsappUrl = `https://wa.me/${phoneNumber}`;
    window.open(whatsappUrl, '_blank').focus();
  }

  document.getElementById('formulariocontacto').addEventListener('submit', function(e) {
    e.preventDefault(); // Evitar el envío estándar del formulario

    // Recoger los datos del formulario
    var formData = new FormData(this);

    // Enviarlos con fetch a tu script PHP
    fetch('https://preinst.cl/apis/email.php', {
        method: 'POST',
        body: formData,
        mode :'no-cors'
    })
    .then(response => {
      console.log(response.body)
    })
    .then(data => {
        console.log(data); // Aquí puedes manejar la respuesta de tu script PHP
    })
    .catch(error => {
        console.error('Error:', error);
    });
});