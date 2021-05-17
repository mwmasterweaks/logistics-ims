<template>
  <div class="container-fluid">
    <div
      class="row main-content bg-success text-center"
      style="max-width:600px"
    >
      <div class="col-md-4 text-center company__info">
        <!-- <span class="company__logo">
          <i class="material-icons">shopping_cart</i>
        </span> -->
        <div class="image">
          <img src="../img/IMS.jpg" width="180" alt="User" />
          <!-- <img src="../img/cart.gif" width="180" alt="User" /> -->
        </div>
        <h5 class="company_title">DCTECH MICRO SERVICES INC.</h5>
      </div>
      <div class="col-md-8 col-xs-12 col-sm-12 login_form">
        <form @submit.prevent="login" class="form-signin">
          <div>
            <div class="body">
              <h1 class="h3 mb-3 font-weight-bold" @click="checkDB">
                LOGISTICS
              </h1>
              <div class="msg">Sign in to start your session</div>

              <hr />

              <div class="input-group">
                <span class="input-group-addon">
                  <i class="material-icons">email</i>
                </span>
                <div class="form-line">
                  <input
                    type="text"
                    ref="email"
                    name="email"
                    class="form-control"
                    v-model.trim="email"
                    v-validate="'required'"
                    placeholder="Email"
                    autocomplete="off"
                    @keyup.enter="login"
                    autofocus
                  />
                </div>
                <small
                  class="text-danger pull-left"
                  v-show="errors.has('email')"
                  >{{ errors.first("email") }}</small
                >
              </div>

              <div class="input-group">
                <span class="input-group-addon">
                  <i class="material-icons">lock</i>
                </span>
                <div class="form-line">
                  <input
                    name="password"
                    type="password"
                    class="form-control"
                    v-validate="'required'"
                    v-model.trim="password"
                    @keyup.enter="login"
                    placeholder="Password"
                  />
                </div>
                <small
                  class="text-danger pull-left"
                  v-show="errors.has('password')"
                  >{{ errors.first("password") }}</small
                >
              </div>

              <div class="row">
                <div class="col-md-6">
                  <small class="text-danger" v-show="login_failed"
                    >Login failed.</small
                  >
                </div>
                <div class="col-md-6">
                  <button
                    class="btn bg-black btn-lg waves-effect waves-light pull-right"
                    type="submit"
                  >
                    LOGIN
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import swal from "sweetalert";

export default {
  name: "Login",
  data() {
    return {
      email: "",
      password: "",
      remember: "",
      isAuth: null,
      amount: {
        subtotal: "0.00",
        tax: "0.00",
        shipping: "0.00",
        total: "0.00"
      },
      login_failed: false
    };
  },

  created() {
    this.isAuth = this.$auth.isAuthenticated();
  },

  methods: {
    login() {
      var subEmail = this.email;

      if (!subEmail.includes("@dctechmicro.com"))
        subEmail += "@dctechmicro.com";

      var data = {
        client_id: 2,
        client_secret: "7vhYZQttQlhd6sAq7Wg1beLIJN31Jyg7PGDyKoQx",
        grant_type: "password",
        username: subEmail,
        password: this.password
      };

      this.$validator.validateAll().then(result => {
        if (result) {
          this.$http
            .post("oauth/token", data)
            .then(response => {
              this.$auth.setToken(
                response.body.access_token,
                response.body.expires_in + Date.now()
              );
              window.location.href = "/";
            })
            .catch(response => {
              console.log(response);
              this.$refs.email.focus();
              this.login_failed = true;
            });
        }
      });
    },
    checkDB() {
      this.$http.get("api/users").then(response => {
        console.log(response.body);
      });
    },
    switchVisibility() {
      this.passwordFieldType =
        this.passwordFieldType === "password" ? "text" : "password";
    }
  }
};
</script>

<style>
.main-content {
  width: 50%;
  border-radius: 20px;
  box-shadow: 0 5px 5px rgba(0, 0, 0, 0.4);
  margin: 5em auto;
  display: flex;
}
.company__info {
  background-color: #2b982b;
  border-top-left-radius: 20px;
  border-bottom-left-radius: 20px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  color: #fff;
}
.fa-android {
  font-size: 3em;
}
@media screen and (max-width: 640px) {
  .main-content {
    width: 90%;
  }
  .company__info {
    display: none;
  }
  .login_form {
    border-top-left-radius: 20px;
    border-bottom-left-radius: 20px;
  }
}
@media screen and (min-width: 642px) and (max-width: 800px) {
  .main-content {
    width: 70%;
  }
}
.row > h5 {
  color: #777575;
  margin-top: 20px;
}
.login_form {
  background-color: #fff;
  border-top-right-radius: 20px;
  border-bottom-right-radius: 20px;
  border-top: 1px solid #ccc;
  border-right: 1px solid #ccc;
}
form {
  padding: 0 2em;
}
.form__input {
  width: 100%;
  border: 0px solid transparent;
  border-radius: 0;
  border-bottom: 1px solid #aaa;
  padding: 1em 0.5em 0.5em;
  padding-left: 2em;
  outline: none;
  margin: 1.5em auto;
  transition: all 0.5s ease;
}
.form__input:focus {
  border-bottom-color: #1cc470;
  box-shadow: 0 0 5px rgba(51, 212, 105, 0.4);
  border-radius: 4px;
}
.btn {
  transition: all 0.5s ease;
  width: 50%;
  border-radius: 30px;
  color: #666363;
  font-weight: 600;
  background-color: #fff;
  border: 1px solid #1cc470;
  margin-top: 1.5em;
  margin-bottom: 1em;
  margin-left: 100px;
}
.btn:hover,
.btn:focus {
  background-color: #1cc470;
  color: #fff;
}
</style>
