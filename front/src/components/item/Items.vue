<template>
  <div class="container-fluid">
    <b-card no-block style="margin-top:-20px">
      <b-tabs small card ref="tabs">
        <!-- MANAGE PRODUCT -->
        <b-tab title="Products" style="cursor:pointer">
          <b-card>
            <h4 style="width:20%">Manage Items</h4>
            <br />
            <div>
              <div>
                <form @submit.prevent="searchItem" style="margin-top:-30px">
                  <br />
                  <div class="row clearfix">
                    <div class="col-md-3">
                      <div class="form-group">
                        <div class="form-line">
                          <span>Search</span>
                          <input
                            id="search"
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
                        <span>Category</span>
                        <div class="form-line">
                          <select
                            class="form-control"
                            v-model="search.category"
                          >
                            <option
                              v-for="category in category"
                              :key="category.id"
                              v-bind:value="category.id"
                            >
                              {{ category.name }}
                            </option>
                            <option value selected>All</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <span>Type</span>
                        <div class="form-line">
                          <select class="form-control" v-model="search.type">
                            <option
                              v-for="type in types"
                              :key="type.id"
                              v-bind:value="type.id"
                            >
                              {{ type.name }}
                            </option>
                            <option value selected>All</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <br />
                      <button
                        type="submit"
                        class="btn bg-black waves-effect waves-light"
                      >
                        Filter
                      </button>

                      <button
                        class="btn btn-success waves-effect"
                        @click="clearSearch"
                      >
                        Clear Filter
                      </button>
                    </div>
                  </div>
                </form>
                <div style="float:right;display:block;margin-top:-66px">
                  <button
                    type="button"
                    class="btn btn-default waves-effect"
                    title="Create New Product"
                    @click="createNewItem"
                    :disabled="!roles.create_item"
                  >
                    <i class="material-icons">note_add</i>
                  </button>
                  <button
                    type="submit"
                    class="btn btn-default waves-effect"
                    title="Print Preview"
                    @click.prevent="print"
                  >
                    <i class="material-icons">print</i>
                  </button>
                  <json-excel
                    class="btn btn-default waves-effect"
                    title="Export to Excel"
                    :data="dataForExcel"
                  >
                    <i class="material-icons">publish</i>
                  </json-excel>
                </div>
              </div>
            </div>
          </b-card>
          <b-card-text>
            <div class="row clearfix" style="margin-top:-25px">
              <div class="col-md-12">
                <div class="table-responsive" id="printable">
                  <div class="table-wrap">
                    <table
                      class="table table-striped table-condensed table-hover"
                      id="itemTable"
                      ref="itemTable"
                    >
                      <thead>
                        <tr>
                          <th
                            class="font-bold"
                            v-for="column in columns"
                            :key="column"
                          >
                            {{ column }}
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <router-link
                          tag="tr"
                          v-for="item in items"
                          :key="item.id"
                          :to="'/items/' + item.id + '/edit'"
                          style="cursor: pointer"
                        >
                          <td>
                            <img
                              v-if="item.image != null"
                              class="img-responsive"
                              :src="$img_path + item.image"
                              width="50"
                              height="50"
                            />
                            <img
                              v-else
                              class="img-responsive"
                              :src="$img_path + '/default.png'"
                              width="50"
                              height="50"
                            />
                          </td>
                          <td>{{ item.id }}</td>
                          <td>{{ item.name }}</td>
                          <td>{{ item.description }}</td>
                          <td>{{ item.category.name }}</td>
                          <td>{{ item.type.name }}</td>
                          <td
                            class="bg-red"
                            v-show="
                              item.forecast.totalItem < 1 &&
                                item.forecast.status == 'no'
                            "
                          >
                            <span>No Stock</span>
                          </td>
                          <td
                            class="bg-orange"
                            v-show="
                              item.forecast.totalItem > 0 &&
                                item.forecast.status == 'no'
                            "
                          >
                            <span>Low Stock</span>
                          </td>
                          <td
                            class="bg-green"
                            v-show="
                              item.forecast.totalItem > 0 &&
                                item.forecast.status == 'ok'
                            "
                          >
                            <span>High Stock</span>
                          </td>
                          <td id="td_qty">
                            <span v-if="item.stocks.length < 1">0</span>
                            <span v-else>{{ item.total_qty }}</span>
                          </td>
                        </router-link>
                        <tr v-show="items.length == 0">
                          <td colspan="14" class="text-center">
                            <small class="col-red">
                              <i>No results found.</i>
                            </small>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                <p>{{ items.length }} items found.</p>
              </div>
            </div>
          </b-card-text>
        </b-tab>
        <!-- END OF PRODUCT TAB -->

        <!-- MANAGE PRODUCT GROUP -->
        <b-tab title="Product Groups" style="cursor:pointer">
          <b-card>
            <h4 style="width:20%">Manage Product Group</h4>
            <br />
            <div>
              <div>
                <form
                  @submit.prevent="searchItemGroup"
                  style="margin-top:-30px"
                >
                  <br />
                  <div class="row clearfix">
                    <div class="col-md-5">
                      <div class="form-group">
                        <div class="form-line">
                          <span>Search</span>
                          <input
                            id="search"
                            type="text"
                            class="form-control"
                            autocomplete="off"
                            @keyup="searchTextGroup"
                            v-model="search2.text2"
                          />
                        </div>
                      </div>
                    </div>

                    <div class="col-md-3">
                      <br />
                      <button
                        type="submit"
                        class="btn bg-black waves-effect waves-light"
                      >
                        Filter
                      </button>

                      <button
                        class="btn btn-success waves-effect"
                        @click="clearSearch"
                      >
                        Clear Filter
                      </button>
                    </div>
                  </div>
                </form>
                <div style="float:right;display:block;margin-top:-66px">
                  <button
                    type="button"
                    class="btn btn-default waves-effect"
                    title="Create New Product"
                    @click="createNewGroup"
                    :disabled="!roles.create_item"
                  >
                    <i class="material-icons">note_add</i>
                  </button>
                  <button
                    type="submit"
                    class="btn btn-default waves-effect"
                    title="Print Preview"
                    @click.prevent="print"
                  >
                    <i class="material-icons">print</i>
                  </button>
                  <json-excel
                    class="btn btn-default waves-effect"
                    title="Export to Excel"
                    :data="dataForExcel"
                  >
                    <i class="material-icons">publish</i>
                  </json-excel>
                </div>
              </div>
            </div>
          </b-card>
          <b-card-text>
            <div class="row clearfix" style="margin-top:-25px">
              <div class="col-md-12">
                <div class="table-responsive" id="printable">
                  <div class="table-wrap">
                    <table
                      class="table table-striped table-condensed table-hover"
                      id="itemGroupTable"
                      ref="itemGroupTable"
                    >
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Item Group</th>
                          <th>Created At</th>
                          <th>Updated At</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="(group, index) in groups"
                          :key="group.index"
                          style="cursor: pointer;"
                          @click="getGroup(index)"
                          data-toggle="modal"
                          data-target="#groupItemModal"
                          tag="row"
                        >
                          <td>{{ group.id }}</td>
                          <td>{{ group.group_name }}</td>
                          <td>{{ group.created_at }}</td>
                          <td>{{ group.updated_at }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                <p>{{ items.length }} items found.</p>
              </div>
            </div>
          </b-card-text>
        </b-tab>
        <!-- END OF PRODUCT GROUP -->
      </b-tabs>
    </b-card>

    <!-- Show Group Modal -->
    <div
      id="groupItemModal"
      class="modal fade"
      tabindex="-1"
      role="dialog"
      data-backdrop="static"
      data-keyboard="false"
    >
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <b>Item Group</b>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form>
            <div class="modal-body">
              <div class="row clearfix">
                <div class="col-sm-12">
                  <label>Group Name</label>
                  <div class="input-group">
                    <div class="form-line">
                      <input
                        type="text"
                        ref="groupItemsName"
                        name="groupItemsName"
                        class="form-control"
                        v-validate="'required'"
                        v-model="groupItems.group_name"
                        autocomplete="off"
                        autofocus="on"
                      />
                    </div>
                    <br />
                    <table
                      class="table table-striped table-condensed table-hover"
                    >
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>ITEM</th>
                          <th>QTY</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="item in groupItems.items"
                          :key="item.item_group_id"
                        >
                          <td>{{ item.id }}</td>
                          <td>{{ item.description }}</td>
                          <td>{{ item.pivot.qty }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- end of show group modal -->

    <div class="modalLoading">
      <!-- Place at bottom of page -->
    </div>
  </div>
</template>

<script>
import swal from "sweetalert";
import JsonExcel from "vue-json-excel";

export default {
  components: {
    "json-excel": JsonExcel
  },

  data() {
    return {
      tabs: [],
      tabCounter: 0,
      printData: {
        id: true,
        name: true,
        desc: true,
        category: true,
        type: false,
        status: false,
        total: false
      },
      columns: [
        "Image",
        "Product Code#",
        "Name",
        "Description",
        "Category",
        "Type",
        "Status",
        "Total"
      ],
      itemGroups: [],
      category: [],
      warehouses: [],
      types: [],
      edit: [],
      roles: [],
      search: {
        item: "",
        category: "",
        type: "",
        sort: "Latest"
      },
      dataForExcel: [],
      groups: [],
      groupItems: [],
      group: {},
      imageFile: null,
      item: {
        name: "",
        items: []
      },
      search: {
        text: "",
        item: ""
      },
      search2: {
        text2: "",
        item: ""
      },

      item_selected: {
        group_name: "",
        orders: []
      }
    };
  },

  mounted() {
    this.fetchDataaa();
    this.item = this.$global.getItems();
    this.loadGroups();
  },
  created() {
    this.items = this.$global.getItems();
    this.types = this.$global.getTypes();
    this.category = this.$global.getCategories();
    this.warehouses = this.$global.getWarehouses();
    this.roles = this.$global.getRoles();
    console.log(this.$img_path);
    $(".page-loader-wrapper").fadeOut();
  },

  methods: {
    getItems() {
      this.$http.get("api/items").then(response => {
        this.items = response.body;
      });
    },
    searchItem() {
      var body = $("body");
      body.addClass("loading");
      this.$http.post("api/items/search", this.search).then(response => {
        var body = $("body");
        console.log(response.body);
        this.items = response.body;
        this.fetchDataaa();
        body.removeClass("loading");
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
    searchTextGroup() {
      var filter, table, tr, targetTableColCount;
      filter = this.search2.text2.toUpperCase();
      table = document.getElementById("itemGroupTable");
      tr = table.getElementsByTagName("row");

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

    searchItemGroup() {
      var body = $("body");
      body.addClass("loading");
      this.$http.post("api/items/search", this.search).then(response => {
        var body = $("body");
        // console.log(response.body);
        this.groups = response.body;
        this.fetchDataaa();
        body.removeClass("loading");
      });
    },
    fetchDataaa() {
      this.dataForExcel = [];
      for (var i = 0; i < this.items.length; i++) {
        var status = "";
        if (
          this.items[i].forecast.totalItem < 1 &&
          this.items[i].forecast.status == "no"
        )
          status = "No Stock";
        if (
          this.items[i].forecast.totalItem > 0 &&
          this.items[i].forecast.status == "no"
        )
          status = "Low Stock";
        if (
          this.items[i].forecast.totalItem > 0 &&
          this.items[i].forecast.status == "ok"
        )
          status = "High Stock";
        this.dataForExcel.push({
          ProductCode: this.items[i].id,
          Name: this.items[i].name,
          Description: this.items[i].description,
          Category: this.items[i].category.name,
          Type: this.items[i].type.name,
          Status: status,
          Quantity: this.items[i].total_qty
        });
      }
    },
    clearSearch() {
      this.search.item = "";
      this.search.category = "";
      this.search.type = "";
      // this.searchItem();
    },
    print() {
      this.$htmlToPaper("printable");
    },

    createNewItem() {
      swal("Create a new product?", {
        buttons: {
          Yes: true,
          cancel: "Cancel"
        }
      }).then(value => {
        switch (value) {
          case "Yes":
            this.$router.push({
              path: "/create/item"
            });
            break;

          default:
            break;
        }
      });
    },
    createNewGroup() {
      swal("Create a new product group?", {
        buttons: {
          Yes: true,
          cancel: "Cancel"
        }
      }).then(value => {
        switch (value) {
          case "Yes":
            this.$router.push({
              path: "/create/itemGroup"
            });
            break;

          default:
            break;
        }
      });
    },

    loadGroups() {
      this.$http.post("api/items/showItemGroup").then(response => {
        this.groups = response.body;
        console.log(response.body);
      });
    },
    getGroup(index) {
      this.groupItems = this.groups[index];
      console.log(this.groupItems);
    },
    create() {
      console.log(this.item_selected.group_name);
      console.log(this.item_selected.orders);

      this.$validator.validateAll().then(result => {
        if (result) {
          swal("Create this item group?", {
            buttons: {
              agree: "Yes",
              cancel: true
            }
          }).then(value => {
            switch (value) {
              case "agree":
                this.$http
                  .post("api/items/addGroup", this.item_selected)
                  .then(response => {
                    swal("A new  item group was successfully added!", {
                      icon: "success"
                    });
                    // this.$http.get("api/items").then(response => {
                    //   this.$global.setItems(response.body);
                    this.$router.push({
                      path: "/inventory"
                    });
                    // });
                  });
                break;
              default:
                break;
            }
          });
        }
      });
    },

    addtoGroup(item) {
      this.item_selected.orders.push({
        id: item.id,
        name: item.name,
        qty: ""
      });

      this.search.item = "";
      this.searchItem();
    },
    searchItem() {
      var filter, table, tr, targetTableColCount;
      filter = this.search.item.toUpperCase();
      table = document.getElementById("tblSearchItem");
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
    removefromSelected(id) {
      for (var index = 0; index < this.item_selected.orders.length; index++) {
        if (this.item_selected.orders[index].id == id) {
          this.item_selected.orders.splice(index, 1);
        }
      }
    },
    exit() {
      swal("Are you sure you want to go back?", {
        icon: "warning",
        buttons: {
          exit: "Yes",
          cancel: true
        }
      }).then(value => {
        switch (value) {
          case "exit":
            this.$router.push({
              path: "/inventory"
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

<style>
.nav-tabs {
  border-bottom: 2px solid #2b982b !important;
}

.nav-tabs .nav-link.active,
.nav-tabs .nav-item.show .nav-link {
  background: #2b982ba4 !important;
  color: #ffffff !important;
  height: 94%;
}

.nav-tabs > li > a {
  border: none !important;
  color: #1e0b0b !important;
  background: transparent !important;
  -webkit-border-radius: 0;
  -moz-border-radius: 0;
  -ms-border-radius: 0;
  border-radius: 0;
  height: 94%;
}

.nav-tabs > li > a:hover,
.nav-tabs > li > a:active,
.nav-tabs > li > a:focus {
  background: #2b982b6e !important;
  height: 94%;
}

textarea {
  resize: none;
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
