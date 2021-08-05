<template>
  <div class="theme-black" id="app">
    <my-login v-if="!isAuth"></my-login>
    <!-- <my-maintenance v-if="!isAuth"></my-maintenance> -->
    <div v-if="isAuth">
      <my-navbar></my-navbar>
      <my-sidebar></my-sidebar>

      <section class="content">
        <router-view></router-view>
      </section>
    </div>
    <!-- <div class="busyPageLoader" v-if="pageBusy">
      <img src="./img/load.gif" style="width:10%" class="imgLoader" />
      <p style="color:black">Loading...</p>
    </div> -->
    <pre-loader v-if="pageBusy"> </pre-loader>
  </div>
</template>

<script>
import Login from "./components/Login.vue";
import Navbar from "./components/Navbar.vue";
import Sidebar from "./components/Sidebar.vue";
import Maintenance from "./components/Maintenance.vue";
import PreLoader from "./components/PreLoader.vue";

export default {
  components: {
    "my-login": Login,
    "my-navbar": Navbar,
    "my-sidebar": Sidebar,
    "my-maintenance": Maintenance,
    "pre-loader": PreLoader
  },

  data() {
    return {
      isAuth: null,
      pageBusy: false
    };
  },

  mounted() {
    this.$root.$on("pageLoading", () => {
      //console.log("pageLoading");
      this.pageBusy = true;
      this.isAuth = null;
    });
    this.$root.$on("pageLoaded", () => {
      //console.log("pageLoaded");
      this.pageBusy = false;
      this.isAuth = this.$auth.isAuthenticated();
    });
  },
  created() {
    this.isAuth = this.$auth.isAuthenticated();
  }
};
</script>

<style>
@import url(./plugins/bootstrap/css/bootstrap.css);
@import url(./plugins/node-waves/waves.css);
@import url(./plugins/animate-css/animate.css);
@import url(./plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css);
@import url(./css/style.css);
@import url(./css/themes/all-themes.css);

.busyPageLoader {
  background-color: rgba(192, 192, 192, 0.3);
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 5000;
}
.imgLoader {
  margin: 0;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
</style>
