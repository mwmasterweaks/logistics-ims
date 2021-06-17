<template>
  <div class="container-fluid">
    <pre-loader></pre-loader>
    <div id="serialPrint" v-show="item.type.name == 'Serialize'">
      <table class="table table-condensed table-hover" id="tblSerial">
        <tbody>
          <tr v-for="serial in serial_to_print" :key="serial.serial1">
            <td>
              <barcode
                v-bind:value="serial.serial1"
                :options="{ width: 2, height: 40, fontSize: 12, textMargin: 6 }"
              ></barcode>
            </td>

            <td>
              <barcode
                v-if="serial.serial2 != null"
                v-bind:value="serial.serial2"
                :options="{ width: 2, height: 40, fontSize: 12, textMargin: 6 }"
              ></barcode>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div id="consumablePrint" v-show="item.type.name == 'Consumable'">
      <table class="table table-condensed table-hover" id="tblConsumable">
        <tbody>
          <tr v-for="index in 7" :key="index">
            <td>
              <barcode v-bind:value="item.id" :options="{}"></barcode>
            </td>

            <td>
              <barcode v-bind:value="item.id" :options="{}"></barcode>
            </td>
            <td>
              <barcode v-bind:value="item.id" :options="{}"></barcode>
            </td>
            <td>
              <barcode v-bind:value="item.id" :options="{}"></barcode>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div id="container">
      <div class="block-header">
        <button
          type="button"
          class="btn btn-default waves-effect"
          @click="exit"
        >
          <i class="material-icons">keyboard_backspace</i>
          <span>Back</span>
        </button>

        <button
          type="button"
          class="btn btn-lg btn-info waves-effect"
          @click="update"
          :disabled="!roles.update_item"
        >
          <span>Save Changes</span>
        </button>
        <button
          type="button"
          class="btn btn-default waves-effect"
          @click="deleteItem(item)"
          :disabled="!roles.delete_item || item.stocks.length > 0"
        >
          <i class="material-icons">delete</i>
          <span>Delete Item</span>
        </button>
      </div>

      <!-- modal for filter -->
      <div
        class="modal fade"
        id="summary"
        tabindex="-1"
        role="dialog"
        data-backdrop="static"
        data-keyboard="false"
      >
        <div
          class="modal-dialog modal-xl"
          role="document"
          style="width:1000px;background:gray"
        >
          <div class="modal-content">
            <div class="modal-header">
              <label :v-model="item.id"
                >{{ item.id }} - {{ item.description }}</label
              >
              <button
                type="button"
                class="close"
                data-dismiss="modal"
                aria-label="Close"
                id="dismiss"
              >
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-25" style="background: #17a2b8;">
                  <label
                    style="float:left;margin-top:8px;font-size:13px;color:#f1e6e6;"
                    >Select Client</label
                  >
                </div>
                <div class="col-50">
                  <model-list-select
                    :list="clients"
                    v-model="clientSelected"
                    option-value="id"
                    option-text="name"
                    style="background:#e4e4e4;"
                  ></model-list-select>
                </div>
                <div class="col-20" style="display:flex">
                  <div style="width:50%;margin-right:5px">
                    <button
                      type="button"
                      class="btn btn-lg btn-success waves-effect"
                      style="width:100%"
                      @click="reset"
                    >
                      <span>Clear Client</span>
                    </button>
                  </div>
                  <div style="width:50%">
                    <button
                      type="button"
                      class="btn btn-lg btn-dark waves-effect"
                      style="width:100%"
                      @click.prevent="printSummary"
                    >
                      <span>Print</span>
                    </button>
                  </div>
                </div>
              </div>
              <br />
              <!-- accordion -->
              <div class="accordion" role="tablist" style="width:100%">
                <b-card no-body class="mb-1">
                  <b-card-header
                    header-tag="header"
                    class="p-1"
                    role="tab"
                    style="height:20px;"
                  >
                    <b-button
                      block
                      v-b-toggle.accordion-1
                      style="background: #17a2b8;color:#f1e6e6"
                      >Select Date Range</b-button
                    >
                  </b-card-header>
                  <b-collapse
                    id="accordion-1"
                    hidden
                    accordion="my-accordion"
                    role="tabpanel"
                  >
                    <b-card-body>
                      <date-range-picker @update="onDateSelected" />
                    </b-card-body>
                  </b-collapse>
                </b-card>
              </div>

              <br />

              <br />
            </div>
          </div>
        </div>
      </div>

      <!-- product details -->
      <div class="card">
        <div class="header">
          <h2>Product/Supply</h2>
        </div>

        <div class="body">
          <!-- ITEM EDIT/DETAILS FORM -->
          <div class="row clearfix">
            <div class="col-md-3">
              <img
                class="img-responsive"
                :src="item.image"
                width="200"
                height="200"
                data-toggle="modal"
                data-target="#modalExpandImage"
                @click="imageExpand"
                style="cursor: pointer"
              />
              <br />
              <p>
                {{ item.id }} - {{ item.name }}
                <br />
                Stock: {{ item.total_qty }}
              </p>
              <p v-show="forecast.totalItem > 0 && forecast.status == 'no'">
                Status:
                <strong class="col-orange">Running low!</strong>
              </p>
              <p v-show="forecast.totalItem < 1 && forecast.status == 'no'">
                Status:
                <strong class="col-red">No Stock!</strong>
              </p>
            </div>
            <div class="col-md-6">
              <div class="row clearfix">
                <div class="col-md-7">
                  <div class="form-group">
                    <label>Product Name</label>
                    <div class="form-line">
                      <input
                        name="name"
                        type="text"
                        class="form-control"
                        v-validate="'required'"
                        v-model="item.name"
                        autocomplete="off"
                        :disabled="!roles.update_item"
                      />
                    </div>
                  </div>
                </div>
              </div>
              <div class="row clearfix">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <div class="form-line">
                      <textarea
                        name="description"
                        type="text"
                        class="form-control"
                        rows="3"
                        v-validate="'required'"
                        v-model="item.description"
                        autocomplete="off"
                        :disabled="!roles.update_item"
                      ></textarea>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row clearfix">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Code</label>
                    <p>{{ item.id }}</p>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Type</label>
                    <p>{{ item.type.name }}</p>
                  </div>
                </div>
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Category</label>
                    <div class="form-line">
                      <select
                        name="category"
                        class="form-control"
                        v-validate="'required'"
                        v-model="item.category.id"
                        :disabled="!roles.update_item"
                      >
                        <option disabled>Please select category</option>
                        <option
                          v-for="category in categories"
                          :key="category.id"
                          v-bind:value="category.id"
                        >
                          {{ category.name }}
                        </option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <barcode v-bind:value="item.id" :options="{}"></barcode>
              <br />
              <button
                v-show="item.type.name == 'Consumable'"
                class="btn btn-default waves-effect pull-left"
                @click="printConsumable"
              >
                Print Preview Barcode
              </button>
            </div>
          </div>
          <!-- END ITEM EDIT FORM -->
          <!-- ITEM STOCKS/LOG DETAILS -->
          <div class="row clearfix">
            <div class="col-md-12">
              <ul class="nav nav-tabs tab-nav-right" role="tablist">
                <li role="presentation" class="active">
                  <a href="#stocks" data-toggle="tab" aria-expanded="true"
                    >Stocks</a
                  >
                </li>
                <li role="presentation" v-show="item.type.name == 'Serialize'">
                  <a href="#serial" data-toggle="tab" aria-expanded="false"
                    >Serial</a
                  >
                </li>
                <li role="presentation">
                  <a href="#log" data-toggle="tab" aria-expanded="false">Log</a>
                </li>
              </ul>
              <div class="tab-content">
                <!-- STOCKS TABLE -->
                <div
                  role="tabpanel"
                  class="tab-pane fade active in"
                  id="stocks"
                >
                  <div class="table-wrap">
                    <table class="table table-condensed table-hover">
                      <thead>
                        <tr>
                          <th>Stock Code#</th>
                          <th>Unit Price</th>
                          <th>Qty In</th>
                          <th>Qty Out</th>
                          <th>Date Received</th>
                          <th>Created At</th>
                          <th>Last Update</th>
                          <th>Receive At</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="stock in item.stocks"
                          :key="stock.id"
                          v-show="item.stocks.length > 0"
                        >
                          <td>{{ stock.id }}</td>
                          <td>{{ stock.price }}</td>
                          <td>{{ stock.qty_in }}</td>
                          <td>{{ stock.qty_out }}</td>
                          <td>{{ stock.received_at }}</td>
                          <td>{{ stock.created_at }}</td>
                          <td>{{ stock.updated_at }}</td>
                          <td>{{ stock.location }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                <!--END STOCKS TABLE -->
                <div
                  role="tabpanel"
                  class="tab-pane fade"
                  id="serial"
                  v-show="item.type.name == 'Serialize'"
                >
                  <div class="row">
                    <div class="col-md-12">
                      <button
                        class="btn btn-default waves-effect pull-right"
                        @click="print"
                      >
                        Print Preview Barcode
                      </button>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <div class="table-wrap">
                        <table
                          class="table table-condensed table-hover"
                          id="tblSerial"
                        >
                          <thead>
                            <tr>
                              <th>Serial</th>
                              <th>Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr
                              v-for="serial in item.serials"
                              :key="serial.serial"
                            >
                              <td>{{ serial.serial }}</td>
                              <td>{{ serial.status }}</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="log">
                  <div class="table-wrap">
                    <table class="table table-condensed table-hover">
                      <thead>
                        <tr>
                          <th>Product Code</th>
                          <th>Serial</th>
                          <th>Status</th>
                          <th>Remarks</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="log in logs" :key="log.id">
                          <td>{{ log.item_id }}</td>
                          <td>{{ log.serial }}</td>
                          <td>{{ log.status }}</td>
                          <td>{{ log.remarks }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!--END ITEM STOCKS/LOG DETAILS -->
        </div>
      </div>
    </div>
    <!-- Generated Summary Invidual-->
    <div class="row clearfix">
      <div class="col-md-12">
        <div class="card">
          <div class="header" style="display:block">
            <h2 style="float:left">Quick Report</h2>

            <div style="float:right">
              <button
                class="btn btn-success waves-effect"
                data-toggle="modal"
                data-target="#summary"
              >
                Generate Summary
              </button>
              <button
                type="submit"
                class="btn bg-black waves-effect waves-light"
                @click.prevent="printSummary"
              >
                Print
              </button>
            </div>
          </div>
          <div class="col-md-12" style="background:green;height:100%">
            <div class="card" id="printable">
              <div class="header text-center">
                <img src="../../img/logo.jpg" width="200px" />
                <br />
                <br />

                <h4 style="color:black">
                  INVENTORY ITEM QUICK REPORT
                </h4>
                <h6>As of {{ this.report_date | moment("MMMM Do YYYY") }}</h6>
              </div>
              <div class="table-wrap">
                <table class="table table-borderless">
                  <thead>
                    <tr>
                      <th>Type</th>
                      <th>Date</th>
                      <th>Number</th>
                      <th>Name</th>
                      <th>Memo</th>
                      <th>Qty</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- part 1-->
                    <template>
                      <tr>
                        <td><strong>Inventory</strong></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                          <strong>{{ reports.stocks }}</strong>
                        </td>
                      </tr>
                      <h6>{{ item.name }} - {{ item.description }}</h6>
                    </template>

                    <!-- invoice or delivery reciepts list -->

                    <tr>
                      <td>
                        <h6>On Hand As Of {{ reports.dateStocks }}</h6>
                      </td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td>{{ reports.stocks }}</td>
                    </tr>

                    <tr v-for="(report, i) in reports.data" :key="`A-${i}`">
                      <td><p>Invoice</p></td>
                      <td>
                        <p>{{ report.date }}</p>
                      </td>
                      <td>
                        <p>{{ report.dr_id }}</p>
                      </td>
                      <td>
                        <p>{{ report.name }}</p>
                      </td>
                      <td>
                        <p>{{ report.description }}</p>
                      </td>
                      <td>
                        <p>-{{ report.qty }}</p>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        Tot On Hand As Of
                        {{ this.report_date | moment("MMMM Do YYYY") }}
                      </td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td>
                        <strong>{{ reports.totalQty }}</strong>
                      </td>
                    </tr>
                    <br />
                    <br />
                    <!-- part 2 -->
                    <template>
                      <h6>On Sales Order</h6>
                    </template>
                    <tr v-for="(report, i) in reports.data" :key="`B-${i}`">
                      <td><p>Sales Order</p></td>
                      <td>
                        <p>{{ report.dateSales }}</p>
                      </td>
                      <td>
                        <p>{{ report.sales_order_id }}</p>
                      </td>
                      <td>
                        <p>{{ report.name }}</p>
                      </td>
                      <td>
                        <p>{{ report.description }}</p>
                      </td>
                      <td>
                        <p>0</p>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        Tot On Sales Order As of
                        {{ this.report_date | moment("MMMM Do YYYY") }}
                      </td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td>
                        <strong>0</strong>
                      </td>
                    </tr>
                    <br />
                    <br />
                    <tr>
                      <td>Total Inventory</td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td>
                        <strong>{{ item.total_qty }}</strong>
                      </td>
                    </tr>
                    <br />
                    <tr>
                      <td>
                        <strong>
                          TOTAL As Of
                          {{
                            this.report_date | moment("MMMM Do YYYY")
                          }}</strong
                        >
                      </td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td>
                        <strong>{{ item.total_qty }}</strong>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <!-- NO CLIENT SELECTED -->
        </div>
      </div>
    </div>
    <!-- Modal ExpandImage -->
    <div id="modalExpandImage" class="modal fade" tabindex="-1">
      <div class="modal-dialog" role="document">
        <div class="modal-content body">
          <div class="modal-body">
            <center>
              <div v-if="imageSelected == null">
                <img class="img-responsive" :src="item.image" />
              </div>
              <div v-else>
                <img class="img-responsive" :src="item.imageSelected" />
              </div>
            </center>
          </div>
          <div class="modal-footer">
            <button
              v-if="imageSelected"
              class="btn btn-lg btn-info waves-effect"
              @click="saveImage"
            >
              Save
            </button>
            <button
              class="btn btn-lg btn-default waves-effect"
              @click="ChangeImageClick"
            >
              Change Image
            </button>
            <input
              id="selectImage"
              type="file"
              accept="image/*"
              @change="imageChange($event)"
              style="display: none"
            />
          </div>
        </div>
      </div>
    </div>
    <!-- END Modal ExpandImage -->
    <div class="checkload">
      <div class="modalLoading">
        <!-- Place at bottom of page -->
      </div>
    </div>
  </div>
</template>

<script>
import swal from "sweetalert";
import PreLoader from "../PreLoader.vue";
import VueRangedatePicker from "vue-rangedate-picker";
import { ModelListSelect } from "vue-search-select";
import DateRangePicker from "vue-mj-daterangepicker";

export default {
  components: {
    "pre-loader": PreLoader,
    "rangedate-picker": VueRangedatePicker,
    "model-list-select": ModelListSelect,
    "data-range-picker": DateRangePicker
  },

  data() {
    return {
      item: {
        id: this.$route.params.item,
        category: {
          id: ""
        },
        type: [],
        stocks: [],
        image: ""
      },
      imageSelected: null,
      categories: [],
      types: [],
      quantities: [],
      warehouses: [],
      suppliers: [],
      forecast: [],
      roles: [],
      logs: [],
      clients: [],
      clientSelected: null,
      dateSelected: null,
      itemSelected: null,
      reports: [],
      report_date: null,
      serial_to_print: [],
      getSummary: {}
    };
  },

  watch: {
    $route(to, from) {
      $(".page-loader-wrapper").fadeIn();
      this.categories = this.$global.getCategories();
      this.types = this.$global.getTypes();
      this.warehouses = this.$global.getWarehouses();
      this.suppliers = this.$global.getSupplier();
      this.getForecast();
      this.getItem();
      this.getLogs();
      this.getToprint();
    }
  },

  created() {},

  beforeMount() {
    this.categories = this.$global.getCategories();
    this.types = this.$global.getTypes();
    this.warehouses = this.$global.getWarehouses();
    this.suppliers = this.$global.getSupplier();
    this.roles = this.$global.getRoles();
    this.getForecast();
    this.getItem();
    this.getLogs();
    this.getToprint();
    this.getClients();
  },

  mounted() {},

  methods: {
    printSummary() {
      this.$htmlToPaper("printable");
    },
    reset() {
      this.clientSelected = "";
    },
    getClients() {
      this.$http.get("api/client").then(response => {
        this.clients = response.body;
      });
    },
    getItem() {
      this.$http.get("api/items/" + this.$route.params.item).then(response => {
        console.log(response.body);
        var temp = response.body;

        if (temp.image == null) temp.image = this.$img_path + "/default.png";
        else temp.image = this.$img_path + temp.image;

        this.item = temp;
        $(".page-loader-wrapper").fadeOut();
      });
    },

    getToprint() {
      this.$http.get("api/logs/to_print/" + this.item.id).then(response => {
        this.serial_to_print = response.body;
      });
    },

    getLogs() {
      this.$http.get("api/logs/" + this.item.id).then(response => {
        this.logs = response.body;
      });
    },

    getForecast() {
      // this.$http
      //   .post("api/notification/forecast/" + this.$route.params.item)
      //   .then(response => {
      //     this.forecast = response.body;
      //   });
    },

    update() {
      this.$validator.validateAll().then(() => {
        this.$http
          .put("api/items/" + this.$route.params.item, this.item)
          .then(response => {
            swal(
              "Updated!",
              this.item.description + " is now updated!",
              "success"
            );
          });
      });
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
            this.$router.go(-1);
            break;

          default:
            break;
        }
      });
    },

    print() {
      // Get HTML to print from element

      document.getElementById("container").style.visibility = "hidden";
      document.getElementById("leftsidebar").style.visibility = "hidden";
      document.getElementById("serialPrint").style.visibility = "visible";
      document.getElementById("serialPrint").style.display = "block";
      document.getElementById("leftsidebar").style.display = "none";
      $(".navbar").css("display", "none");
      window.print();

      document.getElementById("container").style.visibility = "visible";
      document.getElementById("leftsidebar").style.visibility = "visible";
      document.getElementById("serialPrint").style.visibility = "hidden";
      document.getElementById("serialPrint").style.display = "none";
      document.getElementById("leftsidebar").style.display = "block";
      $(".navbar").css("display", "block");
    },

    printConsumable() {
      document.getElementById("container").style.visibility = "hidden";
      document.getElementById("leftsidebar").style.visibility = "hidden";
      document.getElementById("consumablePrint").style.visibility = "visible";
      document.getElementById("consumablePrint").style.display = "block";
      document.getElementById("leftsidebar").style.display = "none";
      $(".navbar").css("display", "none");
      window.print();

      document.getElementById("container").style.visibility = "visible";
      document.getElementById("leftsidebar").style.visibility = "visible";
      document.getElementById("consumablePrint").style.visibility = "hidden";
      document.getElementById("consumablePrint").style.display = "none";
      document.getElementById("leftsidebar").style.display = "block";
      $(".navbar").css("display", "block");
    },

    indexAddOne: function(index) {
      return index + 1;
    },

    deleteItem(item) {
      swal({
        title: "Delete " + item.name + "?",
        text: "Warning, this would delete the item permanently!",
        icon: "warning",
        buttons: true,
        dangerMode: true
      }).then(willDelete => {
        if (willDelete) {
          $(".page-loader-wrapper").fadeIn();
          this.$http
            .delete("api/items/" + item.id)
            .then(response => {
              this.$global.setItems(response.body);
              this.$router.push({
                path: "/inventory"
              });
              swal("Sucessfully deleted " + item.name + "!", {
                icon: "success"
              });
            })
            .catch(response => {
              swal({
                title: "Error",
                text: response.body.error,
                icon: "error",
                dangerMode: true
              }).then(value => {
                if (value) {
                }
              });
            });
        }
      });
    },
    imageChange(e) {
      console.log(e);

      if (e.target.files.length > 0) {
        const file = e.target.files[0];
        this.item.imageSelected = URL.createObjectURL(file);

        var fileReader = new FileReader();
        fileReader.readAsDataURL(e.target.files[0]);

        fileReader.onload = e => {
          this.imageSelected = e.target.result;
          //this.item.image = e.target.result;
        };
      }
    },
    ChangeImageClick() {
      document.getElementById("selectImage").click();
    },
    saveImage() {
      var body = $(".checkload");
      body.addClass("load");
      var temp = this.item.image;
      this.item.image = this.imageSelected;
      this.$http.post("api/items/updateImage", this.item).then(response => {
        console.log(response.body);
        body.removeClass("load");
        swal("Image has been Saved!");
        this.item.image = response.body;
        location.reload();
        // $("#modalExpandImage").modal("hide");
      });
    },
    imageExpand() {
      this.imageSelected = null;
    },
    onDateSelected(values) {
      this.$root.$emit("pageLoading");
      console.log(values);
      this.report_date = values.to;
      // console.log(this.item.id);
      this.getSummary.dateSelected = values;
      this.getSummary.clientSelected = this.clientSelected;
      this.getSummary.itemSelected = this.item.id;
      console.log(this.getSummary.itemSelected);

      this.$http
        .post("api/dashboard/showClientInventoryReport", this.getSummary)
        .then(response => {
          console.log(response.body);
          this.reports = response.body;
        });
      // $("#summary").modal("hide");
      this.$root.$emit("pageLoaded");
      document.getElementById("dismiss").click();
    }
  }
};
</script>

<style scoped>
input {
  width: 70px;
}

#serialPrint,
#consumablePrint {
  position: absolute;
  left: 0;
  visibility: hidden;
  display: none;
}

textarea {
  resize: none;
}

.serial-tbody {
  cursor: pointer;
}

.alert {
  border-radius: 2px;
}

.alert-danger {
  border-left: 4px solid #e4a2a2;
  background-color: #ffeae8 !important;
}

.alert-warning {
  border-left: 4px solid #ffc16c;
  background-color: #ffefd9 !important;
}

.btn-info {
  margin-left: 1em;
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

.modalLoading {
  display: none;
  position: fixed;
  z-index: 1000;
  top: 0;
  left: 0;
  height: 100%;
  width: 100%;
  background: rgba(255, 255, 255, 0.8) url("../../img/loading.gif") 50% 50%
    no-repeat;
}

.checkload > .load > .modalLoading {
  overflow: hidden;
}

.checkload > .load > .modalLoading {
  display: block;
}
.col-25 {
  float: left;
  width: 15%;
  margin-top: 6px;
  margin-left: 15px;
  border-radius: 5px 0 0 5px;
  background: #e4e4e4;
}

.col-50 {
  float: left;
  width: 60%;
  margin-top: 6px;
  margin-left: -5px;
}
.col-20 {
  float: left;
  width: 22%;
  margin-top: 6px;
}
</style>
