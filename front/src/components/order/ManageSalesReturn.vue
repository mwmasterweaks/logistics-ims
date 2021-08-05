<template>
  <div class="container-fluid">
    <div class="block-header">
      <div class="row clearfix">
        <div class="col-md-4">
          <button
            class="btn btn-default waves-effect"
            @click="createSalesReturn"
            :disabled="!roles.create_sales_return"
          >
            <i class="material-icons">note_add</i>
            <span>Create New Sales Return</span>
          </button>
        </div>
      </div>
    </div>
    <div class="card">
      <div class="header">
        <h2>Manage Sales Return</h2>
      </div>
      <div class="body">
        <!-- START SEARCH FORM -->
        <form @submit.prevent="searchSalesReturn">
          <div class="row clearfix">
            <div class="col-md-5">
              <div class="form-group">
                <div class="form-line">
                  <span>Search</span>
                  <input
                    type="text"
                    class="form-control"
                    autocomplete="off"
                    @keyup="searchSalesReturnText"
                    v-model="search.sales_return"
                  />
                </div>
              </div>
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
                  id="SalesReturnTable"
                >
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Client From</th>
                      <th>Returnee</th>
                      <th>Status</th>
                      <th>Remarks</th>
                      <th>Date Return</th>
                    </tr>
                  </thead>
                  <tbody>
                    <router-link
                      tag="tr"
                      :to="'/sales_return/' + sales_return.id"
                      v-for="sales_return in sales_returns"
                      :key="sales_return.id"
                      style="cursor: pointer;"
                    >
                      <td>{{ sales_return.id }}</td>
                      <td>{{ sales_return.from_name }}</td>
                      <td>{{ sales_return.returnee }}</td>
                      <td>{{ sales_return.status }}</td>
                      <td>{{ sales_return.remarks }}</td>
                      <td>{{ sales_return.date_return }}</td>
                    </router-link>
                    <tr v-if="sales_returns.length == 0">
                      <td colspan="6" class="text-center">
                        <small class="col-red">
                          <i>No Sales Return found.</i>
                        </small>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <!-- END ORDER LIST TABLE-->
            <br />
            <p>{{ sales_returns.length }} transmittal found.</p>
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
      sales_returns: [],
      roles: [],
      authenticatedUser: [],
      search: {
        sales_return: "",
        sort: "Latest"
      }
    };
  },

  created() {
    this.roles = this.$global.getRoles();
    this.authenticatedUser = this.$global.getUser();
    this.loadReturns();
  },
  methods: {
    loadReturns() {
      this.$root.$emit("pageLoading");
      this.$http.get("api/SalesReturns").then(response => {
        this.sales_returns = response.body;
        this.$root.$emit("pageLoaded");
      });
    },
    searchSalesReturnText() {
      var filter, table, tr, targetTableColCount;
      filter = this.search.sales_return.toUpperCase();
      table = document.getElementById("SalesReturnTable");
      tr = table.getElementsByTagName("tr");
      for (var i = 0; i < tr.length; i++) {
        var rowData = "";

        if (i == 0) {
          targetTableColCount = table.rows.item(i).cells.length;
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

    createSalesReturn() {
      swal("Create a new sales return?", {
        buttons: {
          Yes: true,
          cancel: "Cancel"
        }
      }).then(value => {
        switch (value) {
          case "Yes":
            this.$router.push({
              path: "/sales_return"
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
