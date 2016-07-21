'use strict';
(function(jQuery){
    // console.log('linkclick');
    var makeLink = function(link){
        let promise = new Promise(function(resolve,reject){
            jQuery.post('/wp-content/plugins/linkclick/ajax.php?func=makeLink',{
                link: link
            },function(result){
                if(!result.error){
                    resolve(result);
                }else{
                    reject();
                }
            })
        });
        return promise;
    }
    tinymce.PluginManager.add('linkclick_button',function(editor,url){
        // console.log(editor,url);
        var linkclick_button_on_click = function(){
            // console.log("linkclick_button_on_click2");
            // console.log(this,editor,url)
            console.log(editor.selection.getContent())
            console.log(editor.selection.getNode());
            console.log(editor.selection.getNode().nodeName);
            let selection = editor.selection;
            let node = selection.getNode();
            if(node.nodeName != 'A' && node.nodeName != 'a') return;
            // console.log(node.href)
            let ol = node.href;
            makeLink(ol).then(function(data){
                let nl = data.link;
                node.href = nl;
                node.setAttribute('data-mce-href',nl);

            })
            // console.log(node.dataset.mceHref)
        }
        editor.addButton('linkclick_button',{
            text: 'LinkClick',
            icon: false,
            onclick:linkclick_button_on_click
        })
    });
})(jQuery);