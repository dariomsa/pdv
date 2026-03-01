<!DOCTYPE html>
<html>
  <head>
    <title>Center an Image using text align center</title>
    <style>
      .img-container {
        text-align: center;
      }
      .nombre{
        position: absolute;
    top: 14%;
    left: 25%;
    font-size: 35px;
    color: white;
    text-transform: uppercase;
      }

      .corral{
    position: absolute;
    top: 32%;
    left: 26%;
    font-size: 23px;
    color: black;
    text-transform: uppercase;
      }

      .categoria{
        position: absolute;
    top: 32.3%;
    left: 60%;
    font-size: 20px;
    color: black;
    text-transform: uppercase;
      }
    </style>
  </head>
  <body>
    <div>
    <div class="img-container"> <!-- Block parent element -->
      <img src="https://quito15krace.com/data/banner/Bienvenido-a-la-15K.jpg" alt="">
      
    </div>
    <div class="img-container nombre" > <!-- Block parent element -->
    @php
    echo ($data_pdf['nombre']);
    @endphp
      
    </div>

    <div class="img-container corral" > <!-- Block parent element -->
    @php
    echo ($data_pdf['corral']);
    @endphp
      
    </div>

    <div class="img-container categoria" > <!-- Block parent element -->
    @php
    echo ($data_pdf['categoria']);
    @endphp
      
    </div>


    </div>
  </body>
</html>