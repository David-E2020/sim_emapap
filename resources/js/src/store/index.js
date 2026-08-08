export const storage = {
    namespaced: true,
    state:{
        drawer:null,
        show_message:false,//para el dialog
        message:null,//contenido del mensaje
        color:'success',
        dialog_confirm:false,// dialog de mesaje de confirmacion
        // title:'',
        // content:'',
        // response:false,
    },
    mutations:{
        updateDrawer(state,drawer){
            state.drawer = drawer;
        },
        updateShowMessage(state,show_message){
            state.show_message = show_message;
        },
        setShowMessage(state,{message,show_message,color}){
            state.message = message;
            state.show_message = show_message;
            state.color = color;
        },
        updateConfirmDialog(state,dialog_confirm){
            state.dialog_confirm = dialog_confirm;
        },

    },
    actions:{
        showMessage({commit},{message,color}){
            commit('setShowMessage', {message:message,show_message:true,color:color});
        },
        // showConfirmDialog({commit},{title,content}){
        //     commit('setShowConfirmDialog',{title,content});
        // }
    }
};
