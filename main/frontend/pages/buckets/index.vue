<template>
  <!--  Handling fetching state.-->
  <v-progress-circular
      v-if="$fetchState.pending"
      :size="50"
      color="secondary"
      indeterminate
  ></v-progress-circular>

  <!--  @TODO: Display proper error.-->
  <p v-else-if="$fetchState.error">An error occurred :(</p>
  <div v-else>
    <v-row>
      <v-col cols="12">
        <h1>Your Buckets</h1>
      </v-col>
    </v-row>


    <!--Add new bucket.-->
    <template>
      <v-dialog
          v-model="addBucket.dialog"
          max-width="600px"
      >
        <template v-slot:activator="{ on, attrs }">
          <v-row>
            <v-col cols="12">
              <v-btn
                  v-bind="attrs"
                  v-on="on"
                  color="primary"
                  dark
              >
                New bucket
              </v-btn>
            </v-col>
          </v-row>

        </template>
        <v-card>
          <form @submit.prevent="addNewBucket">
            <v-card-title>
              <span class="headline">New Bucket</span>
            </v-card-title>
            <v-card-text>


              <v-row>
                <!--Name-->
                <v-col
                    cols="12"
                >
                  <v-text-field
                      v-model="addBucket.data.name"
                      :error-messages="nameErrors"
                      label="Name*"
                      required
                      @blur="$v.addBucket.data.name.$touch()"
                  ></v-text-field>
                </v-col>

                <!--Description-->
                <v-col
                    cols="12"
                >
                  <v-textarea
                      v-model="addBucket.data.description"
                      :error-messages="descriptionErrors"
                      hint="A short description of the bucket"
                      label="Description*"
                      @blur="$v.addBucket.data.description.$touch()"
                  ></v-textarea>
                </v-col>

              </v-row>

              <small>*indicates required field</small>
            </v-card-text>
            <v-card-actions>
              <v-btn
                  :loading="addBucket.loading"
                  color="primary"
                  dark
                  type="submit"
              >
                Create
              </v-btn>
            </v-card-actions>
          </form>
        </v-card>
      </v-dialog>
    </template>


    <v-row>
      <!--If we have errors.-->
      <v-col v-if="buckets.error" cols="12">
        {{ buckets.error }}
      </v-col>

      <!--If there are no buckets.-->
      <v-col v-else-if="buckets.data.length === 0" cols="12">
        You currently don't have any buckets :(
      </v-col>

      <!--List of all buckets-->
      <v-col cols="12">
        <v-list-item v-for="bucket in buckets.data" :key="bucket.id" link two-line>
          <v-list-item-content>
            <nuxt-link :to="`/buckets/${bucket.id}`" class="text-decoration-none white--text">
              <v-list-item-title> {{ bucket.name }}</v-list-item-title>
              <v-list-item-subtitle>{{ bucket.description }}</v-list-item-subtitle>
            </nuxt-link>
          </v-list-item-content>
        </v-list-item>
      </v-col>
    </v-row>

    <!--Snackbar that shows success or error when adding a new bucket.-->
    <v-snackbar
        v-model="addBucket.snackbar.show"
        :timeout="addBucket.snackbar.timeout"
    >
      <span :class="addBucket.snackbar.error ? 'error--text' : 'green--text'">
       {{ addBucket.snackbar.text }}
      </span>
      <template v-slot:action="{ attrs }">
        <v-btn
            v-bind="attrs"
            color="white"
            text
            @click="addBucket.snackbar.show = false"
        >
          Got it
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import {validationMixin} from "vuelidate";
import {required} from "vuelidate/lib/validators";

export default {
  data() {
    return {
      // The buckets of the project. Come from the api.
      buckets: {
        loading: false,
        error: "",
        data: []
      },
      currentProject: "",
      error: "",
      addBucket: {
        dialog: false,
        loading: false,
        error: false,
        // Snackbar that shows error or success when creating new bucket.
        snackbar: {
          timeout: 6000,
          show: false,
          error: false,
          text: ""
        },
        // The form data.
        data: {
          name: "",
          description: ""
        }
      }
    }
  },
  /**
   * ==============================
   * Validation for new bucket.
   * ==============================
   */
  mixins: [validationMixin],
  validations: {
    addBucket: {
      data: {
        name: {required},
        description: {required}
      }
    }
  },
  /**
   * ==============================
   * Methods.
   * ==============================
   */
  methods: {
    /**
     * Add a new bucket.
     */
    async addNewBucket() {

      // Validate the input data.
      this.$v.$touch();
      if (this.$v.$invalid) {
        return;
      }

      // Show loading spinner.
      this.addBucket.loading = true;

      try {
        // Query the api.
        const res = await this.$axios.post(`/api/buckets/`, {
          projectID: this.currentProject,
          name: this.addBucket.data.name,
          description: this.addBucket.data.description,
        });

        // Success.
        if (res.status === 200) {
          // Add new bucket to the list of buckets.
          this.buckets.data.push(res.data);

          // Close the dialog.
          this.addBucket.dialog = false;

          // Show snackbar with success message.
          this.addBucket.snackbar = {
            show: true,
            error: false,
            text: "Bucket created successfully"
          }

          // Don't shw spinner anymore.
          return this.addBucket.loading = false;
        }
      } catch (e) {
        // Show snackbar with error message.
        this.addBucket.snackbar = {
          show: true,
          error: true,
          text: "Something went wrong. Please try again later."
        }

        // Close dialog and don't show spinner anymore.
        this.addBucket.dialog = false;
        return this.addBucket.loading = false;
      }
    }
  },
  /**
   * ==============================
   * Computed.
   * ==============================
   */
  computed: {
    nameErrors() {
      const errors = [];
      if (!this.$v.addBucket.data.name.$dirty) return errors;
      !this.$v.addBucket.data.name.required && errors.push("Name is required");
      return errors;
    },
    descriptionErrors() {
      const errors = [];
      if (!this.$v.addBucket.data.description.$dirty) return errors;
      !this.$v.addBucket.data.description.required && errors.push("Description is required");
      return errors;
    },
  },
  /**
   * ==============================
   * Fetch hook.
   * ==============================
   */
  async fetch() {
    const currentProject = this.$cookies.get('currentProject');
    if (!currentProject) {
      this.buckets.data = false;
      this.currentProject = "";
      return this.error = "No current project selected."
    }

    this.currentProject = currentProject;

    this.buckets.loading = true;

    try {
      // Make api call.
      const res = await this.$axios.get(`/api/buckets/${currentProject}`);

      // Success.
      if (res.status === 200) {
        this.buckets.data = res.data;
        return this.error = "";
      }
      this.buckets.loading = false;
    } catch (e) {
      this.buckets.loading = false;
      return this.error = "Sorry, something went wrong. Please try again later."
    }
  },
  /**
   * ==============================
   * Fetch hook
   * ==============================
   */
  head(){
    return {
      title: 'Buckets'
    }
  }
}
</script>