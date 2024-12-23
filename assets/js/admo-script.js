jQuery(document).ready(function ($) {
  const menuList = document.getElementById("admo-menu-list");

  // Initialize SortableJS
  Sortable.create(menuList, {
    animation: 150,
  });

  // Edit Menu Title
  $(document).on("click", ".edit-menu-item", function () {
    const $li = $(this).closest("li");
    const $menuTitle = $li.find(".menu-title");
    const currentTitle = $menuTitle.text().trim();

    // Replace title with input field
    $menuTitle.html(`<input type="text" value="${currentTitle}" class="menu-title-input" />`);
    $(this).text("Save").removeClass("edit-menu-item").addClass("save-menu-item");
  });

  // Save Menu Title
  $(document).on("click", ".save-menu-item", function () {
    const $li = $(this).closest("li");
    const $input = $li.find(".menu-title-input");
    const newTitle = $input.val();
    const slug = $li.data("slug");

    // Save title via AJAX
    $.post(
      admo_ajax.ajax_url,
      {
        action: "admo_save_title",
        slug: slug,
        new_title: newTitle,
        nonce: admo_ajax.nonce,
      },
      function (response) {
        if (response.success) {
          $li.find(".menu-title").text(newTitle);

          // Usage Example
          AdmoAlertBox.createAlert({
            title: "Success!",
            text: "Your operation was successful.",
            type: "success",
            onConfirm: () => {
              location.reload();
            },
          });
        }
      }
    );

    // Revert button
    $(this).text("Edit").removeClass("save-menu-item").addClass("edit-menu-item");
  });

  // Save order
  $("#admo-save-order").on("click", function () {
    const order = [];
    $("#admo-menu-list li").each(function () {
      const slug = $(this).data("slug");
      order.push(slug);
    });

    $.post(
      admo_ajax.ajax_url,
      {
        action: "admo_save_order",
        order: order,
        nonce: admo_ajax.nonce,
      },
      function (response) {
        if (response.success) {
          AdmoAlertBox.createAlert({
            title: "Success!",
            text: "Your operation was successful.",
            type: "success",
            onConfirm: () => {
              location.reload();
            },
          });
        }
      }
    );
  });
});
