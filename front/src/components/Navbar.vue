<template>
  <div>
    <nav class="navbar navbar-light">
      <div class="container-fluid">
        <!-- <pre-loader></pre-loader> -->
        <div style="background:black">
          <a href="javascript:void(0);" class="bars"></a>
        </div>
        <div class="logo">
          <router-link to="/">
            <a class="navbar-brand" href="javascript:void(0);">
              <img src="../img/ims.gif" width="150" alt="User" />
            </a>
          </router-link>
        </div>
        <div style="margin-right:12px">
          <button
            @click="logout"
            title="Logout"
            class="logout-btn btn waves-effect"
          >
            <i class="fas fa-sign-out-alt align-icon"></i>
            <!-- <span><b>LOGOUT</b></span> -->
          </button>
        </div>
      </div>
    </nav>
    <div class="footer">
      <span class="center-span">
        LOGISTICS IMS &copy;2021
      </span>
    </div>
  </div>
</template>

<script>
import PreLoader from "./PreLoader.vue";

var moment = require("moment");
moment().format();

function showNotification(
  colorName,
  text,
  placementFrom,
  placementAlign,
  animateEnter,
  animateExit
) {
  var allowDismiss = false;

  $.notify(
    {
      message: text
    },
    {
      type: colorName,
      allow_dismiss: allowDismiss,
      newest_on_top: true,
      timer: 300,
      placement: {
        from: placementFrom,
        align: placementAlign
      },
      animate: {
        enter: animateEnter,
        exit: animateExit
      },
      template:
        '<div data-notify="container" class="bootstrap-notify-container alert alert-dismissible {0} ' +
        (allowDismiss ? "p-r-35" : "") +
        '" role="alert">' +
        '<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
        '<span data-notify="icon"></span> ' +
        '<span data-notify="title">{1}</span> ' +
        '<span data-notify="message">{2}</span>' +
        '<div class="progress" data-notify="progressbar">' +
        '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
        "</div>" +
        '<a href="{3}" target="{4}" data-notify="url"></a>' +
        "</div>"
    }
  );
}

export default {
  components: {
    "pre-loader": PreLoader
  },

  data() {
    return {
      authenticatedUser: [],
      roles: [],
      loading_count: 0,
      loading_max: 1,
      notifications: [],
      alerts: []
    };
  },

  created() {
    // this.getNotification();
    this.authenticatedUser = this.$global.getUser();
    this.roles = this.$global.getRoles();
    // console.log(this.authenticatedUser);
    // this.setAuthenticatedUser();
    // this.boot();
    console.log("NAVBAR VUE");
  },

  mounted() {
    // channel.bind("NotifyCreatedSalesOrder", function(data) {
    //   showNotification(
    //     "bg-black",
    //     data.sales_order.user.name + " requested SO#" + data.sales_order.id,
    //     "top",
    //     "left",
    //     "animated bounceInLeft",
    //     "animated fadeOutLeft"
    //   );
    // });
    // channel.bind("NotifyUpdatedSalesOrder", function(data) {
    //   switch (data.sales_order.order_status) {
    //     case "approved":
    //       showNotification(
    //         "bg-black",
    //         "SO#" + data.sales_order.id + " has been approved!",
    //         "top",
    //         "left",
    //         "animated bounceInLeft",
    //         "animated fadeOutLeft"
    //       );
    //       break;
    //     case "order complete":
    //       showNotification(
    //         "bg-black",
    //         "SO#" + data.sales_order.id + " is successfully completed",
    //         "top",
    //         "left",
    //         "animated bounceInLeft",
    //         "animated fadeOutLeft"
    //       );
    //       break;
    //     case "declined":
    //       showNotification(
    //         "bg-black",
    //         "SO#" + data.sales_order.id + " was declined!",
    //         "top",
    //         "left",
    //         "animated bounceInLeft",
    //         "animated fadeOutLeft"
    //       );
    //       break;
    //   }
    // });
    // channel.bind("NotifyCreatedSalesOrder", this.notifyCreate);
    // channel.bind("NotifyUpdatedSalesOrder", this.notifyUpdate);
    // channel.bind("NotifyUpdatedItem", this.notifyItem);
  },

  methods: {
    /*  notifyCreate() {
      //console.log("lalala");
      this.getNotification();
      this.liveUpdate();
    },

    notifyUpdate() {
      this.getNotification();
      this.liveUpdate();
    },

    notifyItem() {
      this.getNotification();
      this.liveUpdate();
    },

    liveUpdate() {
      this.$http.get("api/items").then(response => {
        this.$global.setItems(response.body);
      });

      this.$http.get("api/delivery_receipt").then(response => {
        this.$global.setDeliveryReceipt(response.body);
      });
    }, */

    logout() {
      this.$auth.destroyToken();
      this.$global.destroyGlobal();
      window.location.href = "/login";
    },

    setAuthenticatedUser() {
      this.$http.get("api/user").then(response => {
        this.$global.setUser(response.body);
        this.authenticatedUser = this.$global.getUser();
        this.getUserRoles();
      });
    },

    getUserRoles() {
      this.$http
        .get("api/users/" + this.authenticatedUser.id)
        .then(response => {
          this.$global.setRoles(response.body.roles);
          this.roles = this.$global.getRoles();
        });
    }

    /* getNotification() {
      this.$http.post("api/notification").then(response => {
        this.notifications = response.body;
      });

      this.$http.post("api/notification/alert").then(response => {
        this.alerts = response.body;
      });
    }, */

    /* boot() {
      this.$http.get("api/SalesReturns").then(response => {
        this.$global.setSalesReturn(response.body);

        this.loading_count += 1;
        if (this.loading_count == this.loading_max)
          $(".page-loader-wrapper").fadeOut();
      });

      this.$http.get("api/users").then(response => {
        this.$global.setUsers(response.body);

        this.loading_count += 1;
        if (this.loading_count == this.loading_max)
          $(".page-loader-wrapper").fadeOut();
      });

      this.$http.get("api/items").then(response => {
        this.$global.setItems(response.body);

        this.loading_count += 1;
        if (this.loading_count == this.loading_max)
          $(".page-loader-wrapper").fadeOut();
      });

      this.$http.get("api/category").then(response => {
        this.$global.setCategories(response.body);

        this.loading_count += 1;
        if (this.loading_count == this.loading_max)
          $(".page-loader-wrapper").fadeOut();
      });

      this.$http.get("api/warehouse").then(response => {
        this.$global.setWarehouses(response.body);

        this.loading_count += 1;
        if (this.loading_count == this.loading_max)
          $(".page-loader-wrapper").fadeOut();
      });

      this.$http.get("api/type").then(response => {
        this.$global.setTypes(response.body);

        this.loading_count += 1;
        if (this.loading_count == this.loading_max)
          $(".page-loader-wrapper").fadeOut();
      });

      this.$http.get("api/purchase_order").then(response => {
        this.$global.setPurchaseOrders(response.body);

        this.loading_count += 1;
        if (this.loading_count == this.loading_max)
          $(".page-loader-wrapper").fadeOut();
      });

      this.$http.get("api/supplier").then(response => {
        this.$global.setSupplier(response.body);

        this.loading_count += 1;
        if (this.loading_count == this.loading_max)
          $(".page-loader-wrapper").fadeOut();
      });

      this.$http.get("api/company_assets").then(response => {
        this.$global.setCompanyAssets(response.body);

        this.loading_count += 1;
        if (this.loading_count == this.loading_max)
          $(".page-loader-wrapper").fadeOut();
      });
    } */
  }
};

$(document).ready(function() {
  var down = false;

  $("#bell").click(function(e) {
    var color = $(this).text();
    if (down) {
      $("#box").css("height", "0px");
      $("#box").css("opacity", "0");
      down = false;
    } else {
      $("#box").css("height", "auto");
      $("#box").css("opacity", "1");
      down = true;
    }
  });
});
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Manrope:wght@200&display=swap");

* {
  padding: 0px;
  margin: 0px;
}
.navbar {
  background-color: #b7cdd3;
  z-index: 22;
}
.icon {
  cursor: pointer;
  margin-right: 50px;
  line-height: 60px;
}

.icon span {
  background: #f00;
  padding: 7px;
  border-radius: 50%;
  color: #fff;
  vertical-align: top;
  margin-left: -25px;
}

.icon img {
  display: inline-block;
  width: 26px;
  margin-top: 4px;
}

.icon:hover {
  opacity: 0.7;
}

.logo {
  flex: 1;
  margin-left: 50px;
  height: 50px;
  padding-top: 11px;
}

.notifications {
  width: 300px;
  height: 0px;
  opacity: 0;
  position: absolute;
  top: 63px;
  right: 62px;
  border-radius: 5px 0px 5px 5px;
  background-color: #fff;
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
}

.notifications h2 {
  font-size: 14px;
  padding: 10px;
  border-bottom: 1px solid #eee;
  color: #999;
}

.notifications h2 span {
  color: #f00;
}

.notifications-item {
  display: flex;
  border-bottom: 1px solid #eee;
  padding: 6px 9px;
  margin-bottom: 0px;
  cursor: pointer;
}

.notifications-item:hover {
  background-color: #eee;
}

.notifications-item img {
  display: block;
  width: 50px;
  height: 50px;
  margin-right: 9px;
  border-radius: 50%;
  margin-top: 2px;
}

.notifications-item .text h4 {
  color: #777;
  font-size: 16px;
  margin-top: 3px;
}

.notifications-item .text p {
  color: #aaa;
  font-size: 12px;
}
.footer {
  padding: 14px;
  background: #d7e3e5;
  color: #575757;
  font-weight: bold;
  font-family: "TW Cen MT";
  letter-spacing: 0.15em;
  width: 100%;
  position: fixed;
  bottom: 0;
  display: flex;
  z-index: 20;
}
.center-span {
  flex: 1;
  text-align: right;
}
.logout-btn {
  transition: all 0.5s ease;
  margin-right: 20px;
  box-shadow: 3px 3px 3px 3px #000000;
  border: 0;
  letter-spacing: 0.1em;
  color: #0f0f0f;
  background: #90aeb6;
  /* background-image: linear-gradient(to right, #abc3ca, #ffffff8c, #abc3ca); */
  height: 35px;
  padding-left: 11px;
  padding-right: 8px;
}
.logout-btn:hover {
  color: #000000;
  background: #7e9da7;
  /* background-image: linear-gradient(to right, #9fbac2, #ffffff99, #9fbac2); */
}
.align-icon {
  margin-top: -3px;
}
</style>
