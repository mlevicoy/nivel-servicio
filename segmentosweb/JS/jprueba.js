//cambia los name de los inputs
actualizaName = function ($input)
{
    iNombre = '#componentes1';
    iDosis = '#items1';
    shora1 = '#dias1';
    
 
    for(var i = 1; i <= $input ; i++)
    {
        Nombre = 'componentes';
        Dosis = 'items';
        hora1 = 'dias';
         
        Nombre += i;
        Dosis += i;
        hora1 += i;
         
        var input = $('#input'+i);
 
        input.find(iNombre).attr("name",Nombre);
        input.find(iDosis).attr("name",Dosis);
        input.find(shora1).attr("name",hora1);
        input.find(sminutos1).attr("name",minutos1);
        input.find(shora2).attr("name",hora2);
        input.find(sminutos2).attr("name",minutos2);
        input.find(shora3).attr("name",hora3);
        input.find(sminutos3).attr("name",minutos3);
        input.find(shora4).attr("name",hora4);
        input.find(sminutos4).attr("name",minutos4);
 
    }
}

$(document).ready(function() {
    $('#btnAdd').click(function() {
        var num     = $('.clonedInput').length;
        var newNum  = new Number(num + 1);

        var newElem = $('#input' + num).clone().attr('id', 'input' + newNum);

        newElem.children(':first').attr('id', 'dias' + newNum).attr('name', 'dias' + newNum);
        newElem.children(':first').attr('id', 'componentes' + newNum).attr('name', 'componentes' + newNum);
        newElem.children(':first').attr('id', 'items' + newNum).attr('name', 'items' + newNum);

        $('#input' + num).after(newElem);
        $('#btnDel').attr('disabled','');

        if (newNum == 8)
            $('#btnAdd').attr('disabled','disabled');

        actualizaName(newNum);
    });

    $('#btnDel').click(function() {
        var num = $('.clonedInput').length;

        $('#input' + num).remove();
        $('#btnAdd').attr('disabled','');

        if (num-1 == 1)
            $('#btnDel').attr('disabled','disabled');
    });

    $('#btnDel').attr('disabled','disabled');
});