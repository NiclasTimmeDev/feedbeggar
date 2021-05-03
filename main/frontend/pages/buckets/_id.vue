<template>
  <!--  Handling fetching state.-->
  <v-progress-circular
      v-if="$fetchState.pending"
      :size="50"
      color="secondary"
      indeterminate
  ></v-progress-circular>

  <!--  @TODO: Display proper error.-->
  <p v-else-if="$fetchState.error">{ error || "Sorry, something went wrong. Please try again later." }</p>

  <!--Display the bucket data.-->
  <div v-else>
    <v-row>
      <v-col class="d-flex justify-start align-center" cols="12">

        <h1 class="mr-5">{{ bucket.data.name }}</h1>

        <!--Add new bucket.-->
        <template>
          <v-dialog
              v-model="updateBucket.dialog"
              max-width="600px"
          >
            <template v-slot:activator="{ on, attrs }">
              <v-icon v-bind="attrs"
                      v-on="on"
              >mdi-pencil
              </v-icon>

            </template>
            <v-card>
              <form @submit.prevent="storeBucketUpdate">
                <v-card-title>
                  <span class="headline">Update Bucket</span>
                </v-card-title>
                <v-card-text>
                  <v-row>
                    <!--Name-->
                    <v-col
                        cols="12"
                    >
                      <v-text-field
                          v-model="updateBucket.data.name"
                          :error-messages="nameErrors"
                          label="Name*"
                          required
                          @blur="$v.updateBucket.data.name.$touch()"
                      ></v-text-field>
                    </v-col>

                    <!--Description-->
                    <v-col
                        cols="12"
                    >
                      <v-textarea
                          v-model="updateBucket.data.description"
                          :error-messages="descriptionErrors"
                          hint="A short description of the bucket"
                          label="Description*"
                          @blur="$v.updateBucket.data.description.$touch()"
                      ></v-textarea>
                    </v-col>

                  </v-row>

                  <small>*indicates required field</small>
                </v-card-text>
                <v-card-actions>
                  <v-btn
                      :loading="updateBucket.loading"
                      color="primary"
                      dark
                      type="submit"
                  >
                    Update
                  </v-btn>
                  <v-btn
                      color="error"
                      plain
                      @click="deleteBucket"
                  >
                    Delete
                  </v-btn>
                </v-card-actions>
              </form>
            </v-card>
          </v-dialog>
        </template>
      </v-col>

      <v-col cols="12">
        <p>{{ bucket.data.description }}</p>
      </v-col>
    </v-row>

    <!-- Show list of all feedback.-->
    <!-- If there was an error.-->
    <div v-if="feedback.error">
      {{ feedback.error }}
    </div>

    <!--If there is no feedback.-->
    <div v-else-if="!feedback.data || feedback.data.length === 0">
      There is currently no feedback assigned to this bucket :(.
    </div>

    <!--The list.-->
    <v-row v-else align="center" justify="center">
      <v-col cols="12">
        <v-data-table
            :headers="headers"
            :items="feedback.data"
            :items-per-page="5"
            class="elevation-1"
            @click:row="routeToSingle"
        >
          <template v-slot:item.type="{ item }">

            <!--Badge for feedback type-->
            <v-chip
                color="green"
                dark
            >
              {{ item.type }}
            </v-chip>
          </template>

          <template v-slot:item.created_at="{ item }">
            <span>
            {{ formatDate(new Date(item.created_at)) }}
            </span>
          </template>
        </v-data-table>
      </v-col>
    </v-row>

    <!--Snackbar that shows success or error when updating the bucket.-->
    <v-snackbar
        v-model="updateBucket.snackbar.show"
        :timeout="updateBucket.snackbar.timeout"
    >
      <span :class="updateBucket.snackbar.error ? 'error--text' : 'green--text'">
       {{ updateBucket.snackbar.text }}
      </span>
      <template v-slot:action="{ attrs }">
        <v-btn
            v-bind="attrs"
            color="white"
            text
            @click="updateBucket.snackbar.show = false"
        >
          Got it
        </v-btn>
      </template>
    </v-snackbar>

    <!--    Snackbar when deleting the bucket-->
    <v-snackbar
        v-model="removeBucket.snackbar.show"
        :timeout="removeBucket.snackbar.timeout"
    >
      <span :class="removeBucket.snackbar.error ? 'error--text' : 'green--text'">
      {{ removeBucket.snackbar.text }}
      </span>

      <template v-slot:action="{ attrs }">
        <v-btn
            v-bind="attrs"
            color="secondary"
            text
            @click="interruptDeletion"
        >
          {{ removeBucket.snackbar.error ? "OK" : "Undo" }}
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import DateService from "../../services/DateService";
import {validationMixin} from "vuelidate";
import {required} from "vuelidate/lib/validators";

export default {
  data() {
    return {
      bucketID: "",
      currentProject: "",
      error: "",
      bucket: {
        loading: false,
        error: false,
        data: false,
      },
      // Data for updating bucket.
      updateBucket: {
        dialog: false,
        loading: false,
        error: "",
        snackbar: {
          timeout: 6000,
          show: false,
          error: false,
          text: ""
        },
        data: {
          name: "",
          description: "",
        }
      },
      // Data for deleting bucket.
      removeBucket: {
        loading: false,
        interrupted: false,
        snackbar: {
          timeout: 6000,
          show: false,
          error: false,
          text: ""
        },
      },
      // Feedback belonging to the bucket.
      feedback: {
        loading: false,
        error: false,
        data: false,
      },
      // The headers of the feedback table.
      headers: [
        {
          text: 'Email',
          align: 'start',
          sortable: false,
          value: 'email',
        },
        {
          text: 'Type',
          align: 'start',
          sortable: false,
          value: 'type',
        },
        {
          text: 'Text',
          align: 'start',
          sortable: false,
          value: 'text',
        },
        {
          text: 'Date',
          align: 'start',
          sortable: true,
          value: 'created_at',
          width: "15%"
        },
      ],
    }
  },
  /**
   * ==============================
   * Validation for new bucket.
   * ==============================
   */
  mixins: [validationMixin],
  validations: {
    updateBucket: {
      data: {
        name: {required},
        description: {required}
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
      if (!this.$v.updateBucket.data.name.$dirty) return errors;
      !this.$v.updateBucket.data.name.required && errors.push("Name is required");
      return errors;
    },
    descriptionErrors() {
      const errors = [];
      if (!this.$v.updateBucket.data.description.$dirty) return errors;
      !this.$v.updateBucket.data.description.required && errors.push("Description is required");
      return errors;
    },
  },
  /**
   * ==============================
   * Methods
   * ==============================
   */
  methods: {
    formatDate(date) {
      return DateService.getFormattedDate(date);
    },
    routeToSingle(value) {
      const id = value.id;

      this.$router.push(`/feedback/${id}`);
    },
    /**
     * Send the bucket update to the api.
     */
    async storeBucketUpdate() {

      // Validate the input data.
      this.$v.$touch();
      if (this.$v.$invalid) {
        return;
      }

      this.updateBucket.loading = true;

      try {
        const res = await this.$axios.patch(`/api/buckets/`, {
          bucket_id: this.bucket.id,
          updates: {
            name: this.updateBucket.data.name,
            description: this.updateBucket.data.description
          }
        });

        // Success.
        if (res.status === 200) {
          this.bucket.data = res.data;
          this.updateBucket.loading = true;
          // Show snackbar with success message.
          this.updateBucket.snackbar = {
            show: true,
            error: false,
            text: "Bucket updated successfully"
          }
        }

        this.updateBucket.dialog = false;
      } catch (e) {
        this.updateBucket.loading = false;
        this.updateBucket.snackbar = {
          ...this.updateBucket.snackbar,
          show: true,
          error: true,
          text: "Something went wrong. Please try again later."
        }
        this.updateBucket.dialog = false;
      }
    },
    /**
     * Delete the current bucket.
     */
    async deleteBucket() {
      this.removeBucket.loading = true;
      this.removeBucket.interrupted = false;
      this.removeBucket.snackbar.show = true;
      this.updateBucket.dialog = false;
      this.removeBucket.snackbar.text = "Bucket deleted";

      let _this = this;

      // Wait 6 seconds.
      setTimeout(async function () {

        // Delete feedback if user dit not hit "undo".
        if (!_this.removeBucket.interrupted) {
          try {
            // Query the api and redirect the user.
            const res = await _this.$axios.delete(`/api/buckets/${_this.currentProject}/${_this.bucketID}`);
            if (res.status === 200) {

              _this.$router.push("/buckets");
            }
          } catch (e) {
            //_this.removeBucket.snackbar = {
            //   ..._this.removeBucket.snackbar,
            //   show: true,
            //   error: true,
            //   text: "Someting went wrong. Please try again later."
            // }
          }
        }
      }, this.removeBucket.snackbar.timeout)
    },
    /**
     * Interrupt the deletion of the bucket.
     */
    interruptDeletion() {
      this.removeBucket.interrupted = true;
      this.removeBucket.snackbar.show = false;
    }
  },
  /**
   * ==============================
   * Fetch hook.
   * ==============================
   */
  async fetch() {
    const currentProject = this.$cookies.get('currentProject');
    if (!currentProject) {
      this.bucket.data = false;
      this.currentProject = "";
      return this.error = "No current project selected."
    }

    this.currentProject = currentProject;
    this.bucketID = this.$route.params.id;

    this.bucket.loading = true;
    this.feedback.loading = true;

    try {
      // Make api call to load the bucket data.
      const res = await this.$axios.get(`/api/buckets/${this.currentProject}/${this.bucketID}`);

      // Success.
      if (res.status === 200) {
        this.bucket = {
          ...this.bucket,
          loading: false,
          error: "",
          data: res.data
        };

        // Prepopulate the update data.
        this.updateBucket.data = {
          name: res.data.name,
          description: res.data.description
        }
      }

      // Mae api call to load feedback that belongs to the bucket.
      const resFeedback = await this.$axios.get(`/api/feedback/bucket/${this.currentProject}/${this.bucketID}`);

      // Success.
      if (resFeedback.status === 200) {
        this.feedback = {
          ...this.feedback,
          loading: false,
          error: "",
          data: resFeedback.data
        }
      }

    } catch (e) {
      this.bucket = {
        ...this.bucket,
        loading: false,
        error: "Sorry, something went wrong. Please try again later.",
        data: false
      }
    }
  }
}
</script>