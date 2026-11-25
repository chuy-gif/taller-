@extends('layouts.auth')
@section('titulo', 'Avisos de privacidad')
@section('subtitulo', 'Nexo CRM para Dulce Rincón · Demostración académica')
@section('contenido')
<section class="aviso-privacidad" aria-labelledby="aviso-simplificado">
    <h2 id="aviso-simplificado">Aviso simplificado</h2>
    <p>Esta demostración usa datos ficticios para enseñar cómo se crean cuentas y se gestionan clientes, contactos, pedidos y seguimientos.</p>
    <p>En una operación real, Dulce Rincón debe identificar al responsable y su domicilio. Los datos previstos incluyen información de identificación, contacto y pedidos. Las alergias asociadas a una persona pueden constituir datos sensibles.</p>
    <p>En la demostración puedes limitar la información capturada usando ejemplos ficticios y omitiendo campos opcionales. Para una operación real, el responsable debe habilitar un canal para solicitar la limitación del uso o la divulgación.</p>
    <p><a class="enlace" href="#aviso-integral">Consultar el aviso integral</a></p>
</section>
<section class="aviso-privacidad" aria-labelledby="aviso-integral">
    <h2 id="aviso-integral">Aviso integral</h2>
    <p>Modelo para revisión antes de utilizar el CRM con datos personales reales.</p>
    <h3>Responsable y domicilio</h3>
    <p>Dulce Rincón debe completar su nombre o razón social, domicilio y canal de privacidad antes de adoptar este aviso.</p>
    <h3>Datos y finalidad</h3>
    <p>Se prevé tratar nombre, correo y teléfono, datos de cuenta y rol, contactos del cliente y detalles de cotizaciones, pedidos, anticipos y entregas. El acceso utiliza contraseñas almacenadas como hashes. Estos datos permiten administrar cuentas, dar seguimiento a pedidos y coordinar la atención.</p>
    <p>Las alergias vinculadas con una persona pueden revelar información de salud. Cuando sea indispensable tratarlas, se debe informar esa finalidad y obtener consentimiento expreso y por escrito cuando corresponda. Esta versión académica usa únicamente ejemplos ficticios. El uso propuesto se limita a la operación del CRM, sin finalidades comerciales secundarias.</p>
    <h3>Uso, divulgación y derechos ARCO</h3>
    <p>Una vez habilitado el canal de privacidad, la persona titular podrá solicitar acceso, rectificación, cancelación u oposición, revocar su consentimiento o limitar el uso o divulgación. La solicitud debe indicar el derecho que quiere ejercer, los datos involucrados y un medio de respuesta. El responsable debe habilitar un mecanismo seguro para acreditar la identidad y comunicar el seguimiento.</p>
    <h3>Transferencias y cambios</h3>
    <p>La demostración local no integra servicios de transferencia de datos. Si la operación real incorpora terceros, el responsable deberá informar las transferencias y las opciones que correspondan. Los cambios se publicarán en esta página y el responsable deberá confirmar el medio de comunicación para las personas titulares.</p>
</section>
<p class="ayuda-acceso"><a href="{{ auth()->check() ? route('dashboard') : route('login') }}">Volver a Nexo</a></p>
@endsection
