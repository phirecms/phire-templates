/**
 * Templates Module Scripts for Phire CMS 2
 */

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