<template>
  <div class="container-fluid">
    <div class="block-header" style="margin-top:-20px">
      <div class="row clearfix">
        <div class="col-md-12" style="display:block">
          <router-link
            tag="button"
            class="btn btn-default waves-effect"
            to="/receive_items"
          >
            <i class="material-icons">note_add</i>
            <span>New</span>
          </router-link>
        </div>
      </div>
    </div>
    <div class="card">
      <div class="header">
        <h2>Manage Direct Receives</h2>
      </div>
      <div class="body">
        <form>
          <div class="row clearfix">
            <div class="col-md-5">
              <div class="form-group">
                <div class="form-line">
                  <span>Search</span>
                  <input
                    type="text"
                    class="form-control"
                    autocomplete="off"
                    @input="searchText"
                    v-model="search.text"
                  />
                </div>
              </div>
            </div>
          </div>
        </form>
        <div class="table-wrap">
          <table class="table table-striped" id="itemTable">
            <thead class="thead-dark">
              <tr>
                <th>ID</th>
                <th>User</th>
                <th>Supplier</th>
                <th>Total</th>
                <th>Created At</th>
                <th>Updated At</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(receive, index) in receives"
                :key="index"
                @click="view(index)"
                style="cursor: pointer"
                data-toggle="modal"
                data-target="#reportModal"
              >
                <td>
                  <b>{{ receive.id }}</b>
                </td>
                <td>
                  {{ receive.user.name }}
                </td>
                <td>
                  {{ receive.supplier.name }}
                </td>
                <td>
                  {{ formatPrice(receive.total) }}
                </td>
                <td>
                  {{ receive.created_at }}
                </td>
                <td>
                  {{ receive.updated_at }}
                </td>
                <!-- <td>
                  <b-button
                    variant="dark"
                    title="View"
                    @click="view(index)"
                    data-toggle="modal"
                    data-target="#reportModal"
                    ><i class="material-icons">visibility</i></b-button
                  >

                </td> -->
              </tr>
              <tr v-show="receives.length == 0">
                <td colspan="6" class="text-center">No results found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div id="reportModal" class="modal fade" tabindex="-1">
      <center>
        <div style="width:75%">
          <div class="modal-content">
            <div class="modal-header">
              <button
                type="button"
                class="close"
                data-dismiss="modal"
                aria-label="Close"
              >
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="card">
                <div class="body">
                  <div class="row clearfix">
                    <p style="float:right;text-align:right">
                      Ref.No.<b>{{ receive.id }}</b>
                    </p>
                  </div>
                  <div
                    class="row clearfix"
                    style="margin-top:-20px;display:flex"
                  >
                    <div style="width:15%">
                      <img src="../../img/logo.jpg" />
                    </div>
                    <div
                      style="margin-top:14px;line-height:100%;width:40%;text-align:left"
                    >
                      Dctech Building, Shanghai Street,<br />Matina Aplaya,
                      Davao City<br />Davao Del Sur 8000, Philippines<br />Tel
                      #: (082) 221-2380<br />VAT Registered TIN: 003-375-571-000
                    </div>

                    <div
                      style="margin-top:14px;line-height:100%;width:30%;margin-left:160px;float:right;text-align:left"
                    >
                      <div>
                        <b>{{ receive.supplier.name }} </b><br />
                        Tel #:{{ receive.supplier.contact }} <br />
                        {{ receive.supplier.email }} <br />
                        {{ receive.supplier.address }}<br />
                        Registered TIN:{{ receive.supplier.tin }}
                      </div>
                    </div>
                  </div>
                  <br />
                  <div class="row clearfix" style="display:flex">
                    <div style="width:40%">
                      <p style="text-align:left">
                        Date Received

                        <b-form-datepicker
                          id="datepicker-valid"
                          v-model="receive.created_at"
                          :state="true"
                          disabled
                        ></b-form-datepicker>
                      </p>
                    </div>
                  </div>
                  <div class="row clearfix">
                    <div class="col-md-12 col-sm-12">
                      <div class="table-wrap">
                        <table class="table table-stripped">
                          <thead>
                            <tr>
                              <th>Item</th>
                              <th>Code#</th>
                              <th>Qty</th>
                              <th>Unit Price</th>
                              <th>Receive To</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="item in receive.item" :key="item.id">
                              <td>{{ item.name }} - {{ item.description }}</td>
                              <td>{{ item.id }}</td>
                              <td>{{ item.pivot.qty }}</td>
                              <td>{{ item.pivot.price }}</td>
                              <td>
                                <!-- {{ item.pivot.warehouse_id }} -->
                                <select
                                  v-model="item.pivot.warehouse_id"
                                  v-bind:name="receive.id + 'warehouse_id'"
                                  disabled
                                >
                                  <option
                                    v-for="warehouse in warehouses"
                                    :key="warehouse.id"
                                    v-bind:value="warehouse.id"
                                  >
                                    {{ warehouse.name }}
                                  </option>
                                </select>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-7" style="float:right">
                    <div class="table-responsive">
                      <table class="table">
                        <tbody>
                          <tr></tr>
                          <tr>
                            <th>Total: {{ formatPrice(receive.total) }}</th>
                            <td></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- <div class="modal-footer">
              <input
                type="submit"
                value="Save Changes"
                class="btn btn-lg btn-info waves-effect waves-light pull-right"
                :disabled="!roles.update_category"
              />
            </div> -->
          </div>
        </div>
      </center>
    </div>
  </div>
</template>
<script>
var moment = require("moment");
moment().format();

export default {
  data() {
    return {
      authenticatedUser: [],
      receives: [],
      roles: [],
      warehouses: [],
      search: {
        text: ""
      },
      receive: {
        user: [],
        items: [],
        supplier: []
      }
    };
  },

  beforeMount() {},

  created() {
    this.authenticatedUser = this.$global.getUser();
    this.roles = this.$global.getRoles();
    this.warehouses = this.$global.getWarehouses();
    this.loadReceives();
  },

  mounted() {},

  methods: {
    loadReceives() {
      this.$root.$emit("pageLoading");
      this.$http.get("api/direct_receives").then(response => {
        this.receives = response.body;
        // console.log(this.receives.data);
        this.$root.$emit("pageLoaded");
        console.log(response.body);
      });
    },
    searchText() {
      var filter, table, tr, targetTableColCount;
      filter = this.search.text.toUpperCase();
      table = document.getElementById("itemTable");
      tr = table.getElementsByTagName("tr");

      for (var i = 0; i < tr.length - 1; i++) {
        var rowData = "";

        if (i == 0) {
          targetTableColCount = 9; //table.rows.item(i).cells.length;

          continue; //do not execute further code for header row.
        }
        for (var colIndex = 0; colIndex < targetTableColCount; colIndex++) {
          //console.log(table.rows.item(i).cells.item(colIndex).textContent);
          rowData += table.rows.item(i).cells.item(colIndex).textContent;
        }

        if (rowData.toUpperCase().indexOf(filter) == -1) {
          table.rows.item(i).style.display = "none";
        } else {
          table.rows.item(i).style.display = "table-row";
        }
      }
    },
    view(index) {
      this.receive = this.receives[index];
      console.log(this.receive);
    },
    formatPrice(value) {
      var formatter = new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 2
      });
      return formatter.format(value);
    }
  }
};
</script>

<style scoped>
.table-wrap {
  height: 500px;
  overflow-y: auto;
  overflow-x: hidden;
  border: 1px solid #eee;
}

/* width */
::-webkit-scrollbar {
  width: 5px;
}

/* Track */
::-webkit-scrollbar-track {
  background: #f1f1f1;
}

/* Handle */
::-webkit-scrollbar-thumb {
  background: #888;
}

/* Handle on hover */
::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
