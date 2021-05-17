<template>
  <div class="container-fluid">
    <div class="block-header">
      <div class="row clearfix">
        <div class="col-md-12" style="display:block;margin-top:-20px">
          <button
            class="btn btn-default waves-effect"
            @click="createSalesOrder"
            v-if="roles.create_sales_order"
          >
            <i class="material-icons">note_add</i>
            <span>Create New</span>
          </button>
          <button class="btn btn-default waves-effect disabled" v-else>
            <i class="material-icons">note_add</i>
            <span>Create New Sales Order</span>
          </button>

          <!-- <button class="btn btn-default waves-effect">
            <i class="material-icons">content_copy</i>
            <span>Create Copy</span>
          </button> -->
        </div>
      </div>
    </div>
    <div class="card">
      <div class="header">
        <h2>Manage Sales Orders</h2>
      </div>
      <div class="body">
        <!-- START SEARCH FORM -->
        <form @submit.prevent="searchSalesOrder">
          <div class="row clearfix">
            <div class="col-md-5">
              <div class="form-group">
                <div class="form-line">
                  <span>Search</span>
                  <input
                    type="text"
                    class="form-control"
                    autocomplete="off"
                    @keyup="searchText"
                    v-model="search.text"
                  />
                </div>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <span>Status</span>
                <div class="form-line">
                  <select class="form-control" v-model="search.status">
                    <option value="draft">Draft</option>
                    <option value="approval">For Approval</option>
                    <option value="approved">Approved</option>
                    <option value="declined">Declined</option>
                    <option value="on order">On Order</option>
                    <option value="order complete">Order Complete</option>
                    <option selected>All</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <span>Sort By</span>
                <div class="form-line">
                  <select class="form-control" v-model="search.sort">
                    <option>Latest</option>
                    <option>Oldest</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <br />
              <button class="btn bg-black waves-effect waves-light bg-black">
                Filter
              </button>
              <button class="btn btn-success waves-effect" @click="resetSearch">
                Clear Filter
              </button>
            </div>
          </div>
        </form>
        <!-- END SEARCH FORM -->
        <div class="row clearfix">
          <div class="col-md-12">
            <!-- START ORDER LIST TABLE -->
            <div class="table-wrap">
              <div class="table-responsive">
                <table
                  class="table table-striped table-condensed table-hover"
                  id="SalesOrderTable"
                  ref="SalesOrderTable"
                >
                  <thead>
                    <tr>
                      <th>Sales Order#</th>
                      <th>Received</th>
                      <th>Client</th>
                      <th>Created by</th>
                      <th>Date Created</th>
                      <th>Date Updated</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <router-link
                      tag="tr"
                      v-for="sales_order in sales_orders"
                      :key="sales_order.id"
                      :to="'/sales_order/' + sales_order.id"
                      style="cursor: pointer"
                    >
                      <td>
                        <b>{{ sales_order.id }}</b>
                      </td>
                      <td
                        v-if="
                          sales_order.total_delivered_qty > 0 &&
                            sales_order.total_sales_ordered_qty > 0
                        "
                      >
                        {{ sales_order.percent }}%
                      </td>
                      <td v-else>0%</td>
                      <td v-if="sales_order.client != null">
                        {{ sales_order.client.name }}
                      </td>
                      <td v-else>
                        <small class="col-red">
                          <i>no client selected</i>
                        </small>
                      </td>
                      <td>{{ sales_order.user.name }}</td>
                      <td>
                        <small>{{ sales_order.created_at }}</small>
                      </td>
                      <td>
                        <small>{{ sales_order.updated_at }}</small>
                      </td>
                      <td
                        class="bg-grey"
                        v-show="sales_order.status == 'draft'"
                      >
                        <span>Draft</span>
                      </td>
                      <td
                        class="bg-yellow"
                        v-show="sales_order.status == 'approval'"
                      >
                        <span>For Approval</span>
                      </td>
                      <td
                        class="bg-green"
                        v-show="sales_order.status == 'approved'"
                      >
                        <span>Order: Ongoing</span>
                      </td>
                      <td
                        class="bg-red"
                        v-show="sales_order.status == 'declined'"
                      >
                        <span>Order Declined</span>
                      </td>
                      <td
                        class="bg-green"
                        v-show="sales_order.status == 'on order'"
                      >
                        <span>On Order</span>
                      </td>
                      <td
                        class="bg-green"
                        v-show="sales_order.status == 'order complete'"
                      >
                        <span>Order Fulfilled</span>
                      </td>
                    </router-link>
                    <tr v-show="sales_orders.length == 0">
                      <td colspan="7" class="text-center">
                        <small class="col-red">
                          <i>No orders found.</i>
                        </small>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <!-- END ORDER LIST TABLE-->
            <br />
            <p>{{ sales_orders.length }} orders found.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
var moment = require("moment");
moment().format();

export default {
  data() {
    return {
      sales_orders: [],
      roles: [],
      authenticatedUser: [],
      search: {
        text: "",
        sales_order: "",
        status: "All",
        sort: "Latest"
      }
    };
  },

  created() {
    this.getSalesOrder();
    this.roles = this.$global.getRoles();
    this.authenticatedUser = this.$global.getUser();
  },

  mounted() {
    //channel.bind("NotifyCreatedSalesOrder", this.liveUpdate);
    //channel.bind("NotifyUpdatedSalesOrder", this.liveUpdate);
  },

  methods: {
    getSalesOrder() {
      this.$http.get("api/sales_order").then(response => {
        this.sales_orders = response.body;
        console.log(this.sales_orders);
      });
    },
    liveUpdate() {
      this.$http.get("api/sales_order").then(response => {
        this.sales_orders = response.body;
      });
    },

    searchSalesOrder() {
      this.$http.post("api/sales_order/search", this.search).then(response => {
        this.sales_orders = response.body;
      });
    },

    resetSearch() {
      this.search.sales_order = "";
      this.search.status = "All";
      this.search.sort = "Latest";
    },

    searchText() {
      var filter, table, tr, targetTableColCount;
      filter = this.search.text.toUpperCase();
      table = document.getElementById("SalesOrderTable");
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

    createSalesOrder() {
      swal("Create a new sales order ?", {
        buttons: {
          Yes: true,
          cancel: "Cancel"
        }
      }).then(value => {
        switch (value) {
          case "Yes":
            this.$http
              .post("api/sales_order", this.authenticatedUser)
              .then(response => {
                this.$http.get("api/sales_order").then(response => {
                  this.$global.setSalesOrder(response.body);
                });

                this.$router.push({
                  path: "/sales_order/" + response.body.id
                });
              });
            break;

          default:
            break;
        }
      });
    }
  }
};
</script>

<style scoped>
.ready {
  font-size: 20px;
}

.modal {
  margin-top: 80px;
}

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
