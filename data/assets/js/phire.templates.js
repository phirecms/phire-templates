/**
 * Templates Module Scripts for Phire CMS 2
 */

phire.curTemplateValue;

phire.changeTemplateHistory = function(sel) {
    var id      = jax('#id').val();
    var marked  = jax('#' + sel.id + ' > option:selected').val();

    if (phire.curTemplateValue == undefined) {
        phire.curTemplateValue = jax('#template_source').val();
    }

    if ((marked != 0) && (jax.cookie.load('phire') != '')) {
        var phireCookie = jax.cookie.load('phire');
        var j = jax.json.parse(phireCookie.base_path + phireCookie.app_uri + '/templates/json/' + id + '/' + marked);
        if (j.value != undefined) {
            jax('#template_source').val(j.value);
        }
    } else if (marked == 0) {
        jax('#template_source').val(phire.curTemplateValue);
    }
};

jax(document).ready(function(){
    if (jax('#templates-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#templates-form').checkAll(this.value);
            } else {
                jax('#templates-form').uncheckAll(this.value);
            }
        });
        jax('#templates-form').submit(function(){
            return jax('#templates-form').checkValidate('checkbox', true);
        });
    }
});