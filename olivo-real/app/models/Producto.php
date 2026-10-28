<?php
/**
 * Productos del menú.
 */
class Producto
{
    public static function categorias()
    {
        $categorias = ['Desayunos', 'Entradas', 'Sopas', 'Platos fuertes', 'Cenas', 'Postres', 'Bebidas calientes', 'Bebidas frías'];
        return $categorias;
    }

    /* =========================================================
       AQUÍ AGREGAS TUS PLATOS
       ---------------------------------------------------------
       1) Guarda la foto en la carpeta  img/menu/  (créala si no existe).
          Nombre sin espacios ni acentos:  pollo-al-vino.jpg
       2) Agrega una línea nueva en $productos con este formato:
          ['id' => 65, 'n' => 'Nombre', 'd' => 'Descripción corta', 'cat' => 'Cenas',
           'p' => 9.50, 'img' => 'img/menu/pollo-al-vino.jpg', 'disp' => 1],
       - 'cat' debe ser EXACTAMENTE una de las categorías de Producto::categorias().
       - Si 'img' => null, se muestra el icono de la categoría.
       - 'disp' => 1 disponible, 0 agotado.
       BACKEND: más adelante esto saldrá de las tablas categorias y productos.
       ========================================================= */
    public static function todos()
    {
        $productos = [
          ['id' => 1, 'n' => 'Chilaquiles con huevo', 'd' => 'Totopos en salsa roja con huevo, crema y queso', 'cat' => 'Desayunos', 'p' => 5.50, 'img' => 'img/menu/chilaquiles-con-huevo.jpg', 'disp' => 1],
          ['id' => 2, 'n' => 'Croissant relleno de jamón y queso', 'd' => 'Croissant horneado con jamón y queso derretido', 'cat' => 'Desayunos', 'p' => 4.75, 'img' => 'img/menu/croissant-relleno-de-jamon-y-queso.jpg', 'disp' => 1],
          ['id' => 3, 'n' => 'Desayuno típico salvadoreño', 'd' => 'Huevos, frijoles, plátano frito, crema y tortillas', 'cat' => 'Desayunos', 'p' => 5.50, 'img' => 'img/menu/desayuno-tipico-salvadoreno.jpg', 'disp' => 1],
          ['id' => 4, 'n' => 'Huevos benedictinos con salsa holandesa', 'd' => 'Huevos pochados sobre pan inglés con salsa holandesa', 'cat' => 'Desayunos', 'p' => 7.25, 'img' => 'img/menu/huevos-benedictinos-con-salsa-holandesa.jpg', 'disp' => 1],
          ['id' => 5, 'n' => 'Omelette de queso y espinaca', 'd' => 'Omelette esponjoso relleno de queso y espinaca', 'cat' => 'Desayunos', 'p' => 6.00, 'img' => 'img/menu/omelette-de-queso-y-espinaca.jpg', 'disp' => 1],
          ['id' => 6, 'n' => 'Pancakes con frutas y miel', 'd' => 'Torre de pancakes con frutas frescas y miel', 'cat' => 'Desayunos', 'p' => 5.75, 'img' => 'img/menu/pancakes-con-frutas-y-miel.jpg', 'disp' => 1],
          ['id' => 7, 'n' => 'Tostadas de aguacate con huevo pochado', 'd' => 'Pan artesanal, aguacate y huevo pochado', 'cat' => 'Desayunos', 'p' => 6.50, 'img' => 'img/menu/tostadas-de-aguacate-con-huevo-pochado.jpg', 'disp' => 1],
          ['id' => 8, 'n' => 'Tostadas francesas con fresas', 'd' => 'Pan dorado con fresas frescas y azúcar glas', 'cat' => 'Desayunos', 'p' => 5.50, 'img' => 'img/menu/tostadas-francesas-con-fresas.jpg', 'disp' => 1],
          ['id' => 9, 'n' => 'Bruschettas de tomate y albahaca', 'd' => 'Pan tostado con tomate fresco, albahaca y aceite de oliva', 'cat' => 'Entradas', 'p' => 5.50, 'img' => 'img/menu/bruschettas-de-tomate-y-albahaca.jpg', 'disp' => 1],
          ['id' => 10, 'n' => 'Camarones al ajillo', 'd' => 'Camarones salteados en ajo, aceite de oliva y chile', 'cat' => 'Entradas', 'p' => 9.50, 'img' => 'img/menu/camarones-al-ajillo.jpg', 'disp' => 1],
          ['id' => 11, 'n' => 'Carpaccio de res con parmesano', 'd' => 'Láminas finas de res con parmesano y rúcula', 'cat' => 'Entradas', 'p' => 9.00, 'img' => 'img/menu/carpaccio-de-res-con-parmesano.jpg', 'disp' => 1],
          ['id' => 12, 'n' => 'Champiñones rellenos de queso', 'd' => 'Champiñones gratinados rellenos de queso', 'cat' => 'Entradas', 'p' => 6.50, 'img' => 'img/menu/champinones-rellenos-de-queso.jpg', 'disp' => 1],
          ['id' => 13, 'n' => 'Dedos de queso mozzarella con salsa', 'd' => 'Mozzarella empanizada con salsa para untar', 'cat' => 'Entradas', 'p' => 6.00, 'img' => 'img/menu/dedos-de-queso-mozzarella-con-salsa.jpg', 'disp' => 1],
          ['id' => 14, 'n' => 'Empanadas de pollo', 'd' => 'Empanadas horneadas rellenas de pollo', 'cat' => 'Entradas', 'p' => 5.00, 'img' => 'img/menu/empanadas-de-pollo.jpg', 'disp' => 1],
          ['id' => 15, 'n' => 'Pan de ajo con queso mozzarella', 'd' => 'Pan crujiente con mantequilla de ajo y mozzarella', 'cat' => 'Entradas', 'p' => 4.50, 'img' => 'img/menu/pan-de-ajo-con-queso-mozzarella.jpg', 'disp' => 1],
          ['id' => 16, 'n' => 'Tabla de quesos y embutidos', 'd' => 'Selección de quesos, embutidos, frutos secos y uvas', 'cat' => 'Entradas', 'p' => 14.00, 'img' => 'img/menu/tabla-de-quesos-y-embutidos.jpg', 'disp' => 1],
          ['id' => 17, 'n' => 'Crema de ayote', 'd' => 'Crema suave de ayote con un toque de crema', 'cat' => 'Sopas', 'p' => 4.50, 'img' => 'img/menu/crema-de-ayote.jpg', 'disp' => 1],
          ['id' => 18, 'n' => 'Crema de champiñones', 'd' => 'Crema de champiñones con crutones', 'cat' => 'Sopas', 'p' => 4.75, 'img' => 'img/menu/crema-de-champinones.jpg', 'disp' => 1],
          ['id' => 19, 'n' => 'Crema de tomate asado', 'd' => 'Tomates asados con albahaca y crema', 'cat' => 'Sopas', 'p' => 4.50, 'img' => 'img/menu/crema-de-tomate-asado.jpg', 'disp' => 1],
          ['id' => 20, 'n' => 'Sopa de cebolla gratinada', 'd' => 'Caldo de cebolla caramelizada con queso gratinado', 'cat' => 'Sopas', 'p' => 5.25, 'img' => 'img/menu/sopa-de-cebolla-gratinada.jpg', 'disp' => 1],
          ['id' => 21, 'n' => 'Sopa de frijoles con hierbas', 'd' => 'Frijoles cocidos lentamente con hierbas frescas', 'cat' => 'Sopas', 'p' => 4.25, 'img' => 'img/menu/sopa-de-frijoles-con-hierbas.jpg', 'disp' => 1],
          ['id' => 22, 'n' => 'Sopa de mariscos', 'd' => 'Caldo con camarones, pescado y mariscos del día', 'cat' => 'Sopas', 'p' => 8.50, 'img' => 'img/menu/sopa-de-mariscos.jpg', 'disp' => 1],
          ['id' => 23, 'n' => 'Sopa de pollo con verduras', 'd' => 'Caldo casero de pollo con verduras frescas', 'cat' => 'Sopas', 'p' => 4.75, 'img' => 'img/menu/sopa-de-pollo-con-verduras.jpg', 'disp' => 1],
          ['id' => 24, 'n' => 'Sopa de tortilla mexicana', 'd' => 'Caldo de tomate con tiras de tortilla, aguacate y queso', 'cat' => 'Sopas', 'p' => 5.00, 'img' => 'img/menu/sopa-de-tortilla-mexicana.jpg', 'disp' => 1],
          ['id' => 25, 'n' => 'Filete de res con salsa de pimienta', 'd' => 'Filete de res con salsa cremosa de pimienta', 'cat' => 'Platos fuertes', 'p' => 18.00, 'img' => 'img/menu/filete-de-res-con-salsa-de-pimienta.jpg', 'disp' => 1],
          ['id' => 26, 'n' => 'Lasaña de carne a la boloñesa', 'd' => 'Capas de pasta, salsa boloñesa y queso gratinado', 'cat' => 'Platos fuertes', 'p' => 12.50, 'img' => 'img/menu/lasana-de-carne-a-la-bolonesa.jpg', 'disp' => 1],
          ['id' => 27, 'n' => 'Lomo de cerdo en salsa de ciruelas', 'd' => 'Lomo tierno de cerdo con salsa agridulce de ciruelas', 'cat' => 'Platos fuertes', 'p' => 14.50, 'img' => 'img/menu/lomo-de-cerdo-en-salsa-de-ciruelas.jpg', 'disp' => 1],
          ['id' => 28, 'n' => 'Medallones de res en salsa de vino', 'd' => 'Medallones de res en reducción de vino tinto', 'cat' => 'Platos fuertes', 'p' => 17.50, 'img' => 'img/menu/medallones-de-res-en-salsa-de-vino.jpg', 'disp' => 1],
          ['id' => 29, 'n' => 'Pechuga de pollo en salsa de champiñones', 'd' => 'Pechuga a la plancha con salsa de champiñones', 'cat' => 'Platos fuertes', 'p' => 12.00, 'img' => 'img/menu/pechuga-de-pollo-en-salsa-de-champinones.jpg', 'disp' => 1],
          ['id' => 30, 'n' => 'Pescado a la mantequilla con puré de papa', 'd' => 'Filete de pescado en mantequilla con puré cremoso', 'cat' => 'Platos fuertes', 'p' => 14.00, 'img' => 'img/menu/pescado-a-la-mantequilla-con-pure-de-papa.jpg', 'disp' => 1],
          ['id' => 31, 'n' => 'Risotto de champiñones y parmesano', 'd' => 'Arroz cremoso con champiñones y parmesano', 'cat' => 'Platos fuertes', 'p' => 13.00, 'img' => 'img/menu/risotto-de-champinones-y-parmesano.jpg', 'disp' => 1],
          ['id' => 32, 'n' => 'Salmón a la plancha con vegetales', 'd' => 'Salmón a la plancha con vegetales salteados', 'cat' => 'Platos fuertes', 'p' => 16.00, 'img' => 'img/menu/salmon-a-la-plancha-con-vegetales.jpg', 'disp' => 1],
          ['id' => 33, 'n' => 'Bowl de quinoa con vegetales asados', 'd' => 'Quinoa con vegetales asados y aderezo de limón', 'cat' => 'Cenas', 'p' => 9.50, 'img' => 'img/menu/bowl-de-quinoa-con-vegetales-asados.jpg', 'disp' => 1],
          ['id' => 34, 'n' => 'Crepas saladas de pollo y champiñones', 'd' => 'Crepas rellenas de pollo y champiñones en salsa blanca', 'cat' => 'Cenas', 'p' => 8.50, 'img' => 'img/menu/crepas-saladas-de-pollo-y-champinones.jpg', 'disp' => 1],
          ['id' => 35, 'n' => 'Ensalada caprese con mozzarella fresca', 'd' => 'Tomate, mozzarella fresca, albahaca y aceite de oliva', 'cat' => 'Cenas', 'p' => 8.00, 'img' => 'img/menu/ensalada-caprese-con-mozzarella-fresca.jpg', 'disp' => 1],
          ['id' => 36, 'n' => 'Ensalada César con pollo a la parrilla', 'd' => 'Lechuga romana, pollo a la parrilla, crutones y parmesano', 'cat' => 'Cenas', 'p' => 9.00, 'img' => 'img/menu/ensalada-cesar-con-pollo-a-la-parrilla.jpg', 'disp' => 1],
          ['id' => 37, 'n' => 'Panini de pollo al pesto', 'd' => 'Pan ciabatta con pollo, pesto y queso', 'cat' => 'Cenas', 'p' => 8.50, 'img' => 'img/menu/panini-de-pollo-al-pesto.jpg', 'disp' => 1],
          ['id' => 38, 'n' => 'Quiche de espinaca y queso', 'd' => 'Tarta horneada de espinaca y queso', 'cat' => 'Cenas', 'p' => 7.50, 'img' => 'img/menu/quiche-de-espinaca-y-queso.jpg', 'disp' => 1],
          ['id' => 39, 'n' => 'Sándwich club de pavo', 'd' => 'Pavo, tocino, lechuga y tomate en pan tostado', 'cat' => 'Cenas', 'p' => 8.00, 'img' => 'img/menu/sandwich-club-de-pavo.jpg', 'disp' => 1],
          ['id' => 40, 'n' => 'Wrap de pollo y vegetales', 'd' => 'Tortilla rellena de pollo y vegetales frescos', 'cat' => 'Cenas', 'p' => 8.00, 'img' => 'img/menu/wrap-de-pollo-y-vegetales.jpg', 'disp' => 1],
          ['id' => 41, 'n' => 'Brownie de chocolate con helado', 'd' => 'Brownie tibio con helado de vainilla', 'cat' => 'Postres', 'p' => 5.50, 'img' => 'img/menu/brownie-de-chocolate-con-helado.jpg', 'disp' => 1],
          ['id' => 42, 'n' => 'Cheesecake de frutos rojos', 'd' => 'Cheesecake cremoso con salsa de frutos rojos', 'cat' => 'Postres', 'p' => 5.75, 'img' => 'img/menu/cheesecake-de-frutos-rojos.jpg', 'disp' => 1],
          ['id' => 43, 'n' => 'Crepas dulces con Nutella y banano', 'd' => 'Crepas rellenas de Nutella y banano', 'cat' => 'Postres', 'p' => 5.50, 'img' => 'img/menu/crepas-dulces-con-nutella-y-banano.jpg', 'disp' => 1],
          ['id' => 44, 'n' => 'Flan de caramelo', 'd' => 'Flan casero con caramelo', 'cat' => 'Postres', 'p' => 3.75, 'img' => 'img/menu/flan-de-caramelo.jpg', 'disp' => 1],
          ['id' => 45, 'n' => 'Panna cotta de vainilla con fresas', 'd' => 'Panna cotta de vainilla con salsa de fresas', 'cat' => 'Postres', 'p' => 4.75, 'img' => 'img/menu/panna-cotta-de-vainilla-con-fresas.jpg', 'disp' => 1],
          ['id' => 46, 'n' => 'Tiramisú italiano', 'd' => 'Postre italiano de café, mascarpone y cacao', 'cat' => 'Postres', 'p' => 5.50, 'img' => 'img/menu/tiramisu-italiano.jpg', 'disp' => 1],
          ['id' => 47, 'n' => 'Tres leches', 'd' => 'Bizcocho bañado en tres leches con crema', 'cat' => 'Postres', 'p' => 4.50, 'img' => 'img/menu/tres-leches.jpg', 'disp' => 1],
          ['id' => 48, 'n' => 'Volcán de chocolate', 'd' => 'Bizcocho de chocolate con centro fundido', 'cat' => 'Postres', 'p' => 6.00, 'img' => 'img/menu/volcan-de-chocolate.jpg', 'disp' => 1],
          ['id' => 49, 'n' => 'Café americano', 'd' => 'Café de la casa recién preparado', 'cat' => 'Bebidas calientes', 'p' => 2.00, 'img' => 'img/menu/cafe-americano.jpg', 'disp' => 1],
          ['id' => 50, 'n' => 'Cappuccino', 'd' => 'Espresso con leche vaporizada y espuma', 'cat' => 'Bebidas calientes', 'p' => 3.00, 'img' => 'img/menu/cappuccino.jpg', 'disp' => 1],
          ['id' => 51, 'n' => 'Chocolate caliente', 'd' => 'Chocolate espeso con leche', 'cat' => 'Bebidas calientes', 'p' => 3.25, 'img' => 'img/menu/chocolate-caliente.jpg', 'disp' => 1],
          ['id' => 52, 'n' => 'Espresso', 'd' => 'Café concentrado, intenso y aromático', 'cat' => 'Bebidas calientes', 'p' => 2.00, 'img' => 'img/menu/espresso.jpg', 'disp' => 1],
          ['id' => 53, 'n' => 'Latte de vainilla', 'd' => 'Espresso con leche y jarabe de vainilla', 'cat' => 'Bebidas calientes', 'p' => 3.50, 'img' => 'img/menu/latte-de-vainilla.jpg', 'disp' => 1],
          ['id' => 54, 'n' => 'Mocaccino', 'd' => 'Espresso, chocolate y leche vaporizada', 'cat' => 'Bebidas calientes', 'p' => 3.50, 'img' => 'img/menu/mocaccino.jpg', 'disp' => 1],
          ['id' => 55, 'n' => 'Té de manzanilla', 'd' => 'Infusión suave de manzanilla', 'cat' => 'Bebidas calientes', 'p' => 2.00, 'img' => 'img/menu/te-de-manzanilla.jpg', 'disp' => 1],
          ['id' => 56, 'n' => 'Té verde', 'd' => 'Infusión de té verde', 'cat' => 'Bebidas calientes', 'p' => 2.00, 'img' => 'img/menu/te-verde.jpg', 'disp' => 1],
          ['id' => 57, 'n' => 'Café frío con leche', 'd' => 'Café frío con leche y hielo', 'cat' => 'Bebidas frías', 'p' => 3.25, 'img' => 'img/menu/cafe-frio-con-leche.jpg', 'disp' => 1],
          ['id' => 58, 'n' => 'Frappé de caramelo', 'd' => 'Frappé cremoso con caramelo y crema batida', 'cat' => 'Bebidas frías', 'p' => 4.50, 'img' => 'img/menu/frappe-de-caramelo.jpg', 'disp' => 1],
          ['id' => 59, 'n' => 'Jugo de fresa', 'd' => 'Jugo natural de fresa', 'cat' => 'Bebidas frías', 'p' => 3.25, 'img' => 'img/menu/jugo-de-fresa.jpg', 'disp' => 1],
          ['id' => 60, 'n' => 'Jugo natural de naranja', 'd' => 'Naranja recién exprimida', 'cat' => 'Bebidas frías', 'p' => 3.00, 'img' => 'img/menu/jugo-natural-de-naranja.jpg', 'disp' => 1],
          ['id' => 61, 'n' => 'Licuado de fresa y banano', 'd' => 'Licuado de fresa y banano con leche', 'cat' => 'Bebidas frías', 'p' => 3.75, 'img' => 'img/menu/licuado-de-fresa-y-banano.jpg', 'disp' => 1],
          ['id' => 62, 'n' => 'Limonada con hierbabuena', 'd' => 'Limonada natural con hierbabuena fresca', 'cat' => 'Bebidas frías', 'p' => 3.25, 'img' => 'img/menu/limonada-con-hierbabuena.jpg', 'disp' => 1],
          ['id' => 63, 'n' => 'Limonada natural', 'd' => 'Limonada recién preparada', 'cat' => 'Bebidas frías', 'p' => 3.00, 'img' => 'img/menu/limonada-natural.jpg', 'disp' => 1],
          ['id' => 64, 'n' => 'Té helado de limón', 'd' => 'Té frío con limón y hielo', 'cat' => 'Bebidas frías', 'p' => 2.75, 'img' => 'img/menu/te-helado-de-limon.jpg', 'disp' => 1],
        ];
        return $productos;
    }

    // BACKEND: solo productos disponibles. Versión corta para tomar pedidos y para los reportes.
    public static function paraPedidos()
    {
        return [
          ['id' => 1, 'n' => 'Ensalada César',      'cat' => 'Entradas',       'p' => 6.50],
          ['id' => 2, 'n' => 'Salmón a la plancha', 'cat' => 'Platos fuertes', 'p' => 14.00],
          ['id' => 3, 'n' => 'Pasta Alfredo',       'cat' => 'Platos fuertes', 'p' => 12.50],
          ['id' => 4, 'n' => 'Filete de res',       'cat' => 'Platos fuertes', 'p' => 18.00],
          ['id' => 5, 'n' => 'Limonada natural',    'cat' => 'Bebidas',        'p' => 3.00],
          ['id' => 6, 'n' => 'Coca Cola',           'cat' => 'Bebidas',        'p' => 2.50],
          ['id' => 7, 'n' => 'Tiramisú',            'cat' => 'Postres',        'p' => 5.50],
        ];
    }
}
