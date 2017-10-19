/**
 * Created by Nabeel on 2016-02-02.
 */
!function(a,b,c){a(function(){
//Initiate Color Picker
a(".wp-color-picker-field").wpColorPicker();
// Switches option sections
var c=a(".group").hide(),d="";"undefined"!=typeof localStorage&&(d=localStorage.getItem("mkecs_active_tab")),
// if url has section id as hash then set it as active or override the current local storage value
b.location.hash&&(d=b.location.hash,"undefined"!=typeof localStorage&&localStorage.setItem("mkecs_active_tab",d)),""!==d&&a(d).length?
// open target tab
a(d).fadeIn():
// open first tab's group
c.first().fadeIn(),c.find(".collapsed").each(function(){a(this).find("input:checked").parent().parent().parent().nextAll().each(function(){return a(this).hasClass("last")?(a(this).removeClass("hidden"),!1):void a(this).filter(".hidden").removeClass("hidden")})}),""!==d&&a(d+"-tab").length?a(d+"-tab").addClass("nav-tab-active"):a(".nav-tab-wrapper a:first").addClass("nav-tab-active");var e=a(".nav-tab-wrapper a");e.click(function(b){b.preventDefault(),e.removeClass("nav-tab-active");var d=a(this).addClass("nav-tab-active").blur(),f=d.attr("href");"undefined"!=typeof localStorage&&localStorage.setItem("mkecs_active_tab",f),c.hide(),a(f).fadeIn()}),a(".mkecs-browse").on("click",function(b){b.preventDefault();var c=a(this),d=wp.media.frames.file_frame=wp.media({title:c.data("uploader_title"),button:{text:c.data("uploader_button_text")},multiple:!1});d.on("select",function(){var a=d.state().get("selection").first().toJSON();c.prev(".mkecs-url").val(a.url).change()}),
// Finally, open the modal
d.open()})})}(jQuery,window);