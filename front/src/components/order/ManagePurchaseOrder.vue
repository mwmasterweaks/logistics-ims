<template>
  <div class="container-fluid">
    <div class="block-header" style="margin-top:-20px">
      <div class="row clearfix">
        <div class="col-md-12" style="display:block">
          <button
            type="button"
            class="btn btn-default waves-effect"
            @click="createPurchaseOrder"
          >
            <i class="material-icons">note_add</i>
            <span>New</span>
          </button>
          <!-- <router-link
            tag="button"
            class="btn btn-default waves-effect"
            to="/receive_items"
          >
            <i class="material-icons">call_received</i>
            <span>Direct Receive Items</span>
          </router-link> -->
          <!-- <router-link
            tag="button"
            class="btn btn-default waves-effect"
            to="/report"
          >
            <i class="material-icons">summarize</i>
            <span>Generate Report</span>
          </router-link> -->
        </div>
      </div>
    </div>
    <div class="card">
      <div class="header">
        <h2>Manage Purchase Orders</h2>
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
                    @keyup="searchText"
                    v-model="search.text"
                  />
                </div>
              </div>
            </div>
          </div>
        </form>
        <div class="table-wrap">
          <table class="table table-striped" id="itemTable" ref="itemTable">
            <thead class="thead-dark">
              <tr>
                <th>Purchase Order</th>
                <th>Supplier</th>
                <th>Received</th>
                <th>Total</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <router-link
                tag="tr"
                v-for="purchase_order in purchase_orders"
                :key="purchase_order.id"
                :to="'/purchase_order/' + purchase_order.id"
                style="cursor: pointer"
              >
                <td>
                  <b>{{ purchase_order.id }}</b>
                </td>
                <td v-if="purchase_order.supplier">
                  {{ purchase_order.supplier.name }}
                </td>
                <td v-else>
                  <small class="col-red">
                    <i>no supplier selected</i>
                  </small>
                </td>
                <td
                  v-if="
                    purchase_order.total_qty_received > 0 &&
                      purchase_order.total_qty_ordered > 0
                  "
                >
                  {{
                    (purchase_order.total_qty_received /
                      purchase_order.total_qty_ordered) *
                      100
                  }}%
                </td>
                <td v-else>0%</td>
                <td>
                  <small>{{ formatPrice(purchase_order.total) }}</small>
                </td>
                <td class="bg-grey" v-show="purchase_order.status == 'draft'">
                  <span>Draft</span>
                </td>
                <td
                  class="bg-yellow"
                  v-show="purchase_order.status == 'approval'"
                >
                  <span>For Approval</span>
                </td>
                <td
                  class="bg-green"
                  v-show="purchase_order.status == 'approved'"
                >
                  <span>Order Approved</span>
                </td>
                <td class="bg-red" v-show="purchase_order.status == 'declined'">
                  <span>Order Declined</span>
                </td>
                <td
                  class="bg-green"
                  v-show="purchase_order.status == 'on order'"
                >
                  <span>On Order</span>
                </td>
                <td
                  class="bg-green"
                  v-show="purchase_order.status == 'order complete'"
                >
                  <span>Order Fulfilled</span>
                </td>
              </router-link>
              <tr v-show="purchase_orders.length == 0">
                <td colspan="6" class="text-center">No results found.</td>
              </tr>
            </tbody>
          </table>
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
      authenticatedUser: [],
      purchase_orders: [],
      search: {
        text: ""
      }
    };
  },

  beforeMount() {},

  created() {
    this.authenticatedUser = this.$global.getUser();
    this.purchase_orders = this.$global.getPurchaseOrders();
  },

  mounted() {},

  methods: {
    createPurchaseOrder() {
      swal("Create a new purchase order ?", {
        buttons: {
          Yes: true,
          cancel: "Cancel"
        }
      }).then(value => {
        switch (value) {
          case "Yes":
            this.$http
              .post("api/purchase_order", this.authenticatedUser)
              .then(response => {
                this.$http.get("api/purchase_order").then(response => {
                  this.$global.setPurchaseOrders(response.body);
                });

                this.$router.push({
                  path: "purchase_order/" + response.body.id
                });
              });
            break;

          default:
            break;
        }
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
