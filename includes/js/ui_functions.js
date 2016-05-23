/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var vals_soc_ui = {
    onChangeAvailability : function(elem, id){
        if (elem.value == 1){makeVisible(id);} 
        else {makeInvisible(id);}
    }
};
