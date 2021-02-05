/*!
 * @package    contactus
 * @copyright  Copyright (c)2013-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

if (typeof akeeba == "undefined")
{
    var akeeba = {};
}

if (typeof akeeba.ContactUs == "undefined")
{
    akeeba.ContactUs = {};
}

if (typeof akeeba.ContactUs.Frontend == "undefined")
{
    akeeba.ContactUs.Frontend = {};
}

akeeba.ContactUs.onCategoryChange = function (event)
{
    var elSelect   = document.getElementById('contactus_category_id');
    var categoryId = elSelect.value;

    var elEncrypted   = document.getElementById("comContactUsMessageEncrypted");
    var elUnencrypted = document.getElementById("comContactUsMessageUnencrypted");

    var encryptedCategories = Joomla.getOptions("com_contactus.encryptedCategories", []);
    var isEncrypted         = encryptedCategories.indexOf(categoryId) !== -1;

    elEncrypted.style.display   = "none";
    elUnencrypted.style.display = "none";

    if (isEncrypted)
    {
        elEncrypted.style.display = "block";
    }
    else
    {
        elUnencrypted.style.display = "block";
    }
}

window.jQuery(document).ready(function ()
{
    window.jQuery("#contactus_category_id").on('change', akeeba.ContactUs.onCategoryChange);
    window.jQuery("#contactus_category_id").trigger('change');
});
