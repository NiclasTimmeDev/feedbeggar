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

  <div v-else>
    <h1>You got some Feedback!</h1>

    <!--Chip-->
    <v-row>
      <v-col cols="12">
        <type-badge :type="feedback.type"></type-badge>
      </v-col>
    </v-row>

    <!--Top Bar-->
    <v-row>

      <!--EMAIL-->
      <v-col cols="12" sm="3">
        <h4>EMAIL</h4>
        <div>{{ feedback.email || "Anonymous" }}</div>
      </v-col>

      <!--PAGE-->
      <v-col cols="12" sm="3">
        <h4>PAGE</h4>
        <div>{{ feedback.path }}</div>
      </v-col>

      <!--DEVICE-->
      <v-col cols="12" sm="3">
        <h4>DEVICE</h4>
        <div>{{ feedback.browser_name }}, {{ feedback.browser_version }}, {{ feedback.os_name }}</div>
      </v-col>

      <!--DATE-->
      <v-col cols="12" sm="3">
        <h4>DATE</h4>
        <div>{{ formatDate(new Date(feedback.created_at)) }}</div>
      </v-col>

    </v-row>

    <!--TEXT-->
    <v-row>
      <v-col cols="12">
        <h4>TEXT</h4>
        {{ feedback.text }}
      </v-col>
    </v-row>

    <!--PICTURE-->
    <v-row v-if="feedback.screenshot">
      <v-col cols="12">
        <v-img :lazy-src="feedback.screenshot"
               :src="feedback.screenshot"
               alt="Feedback screenshot"
               contain
        >
        </v-img>
      </v-col>
    </v-row>

    <!--ACTIONS-->
    <v-row>
      <!-- Add to bucket -->
      <v-col cols="12">
        <v-dialog
            v-model="buckets.dialog"
            max-width="600"
            transition="dialog-top-transition"
        >
          <template v-slot:activator="{ on, attrs }">
            <v-btn
                v-bind="attrs"
                v-on="on"
                color="primary"
                @click="getBuckets"
            >Add to bucket
            </v-btn>
          </template>
          <template>
            <v-card>
              <v-toolbar
                  color="secondary"
                  dark
              ><h3>Add this feedback to one of your buckets</h3></v-toolbar>
              <v-card-text>
                <v-progress-circular
                    v-if="buckets.loading"
                    :size="50"
                    color="secondary"
                    indeterminate
                ></v-progress-circular>
                <div v-else-if="buckets.error">
                  Sorry, something went wrong :(
                </div>
                <div v-else-if="buckets.data.length !== 0">
                  <v-radio-group v-model="buckets.selected">
                    <v-radio
                        v-for="bucket in buckets.data"
                        :key="bucket.id"
                        :label="bucket.name"
                        :value="bucket.id"
                    ></v-radio>
                  </v-radio-group>
                </div>
              </v-card-text>
              <v-card-actions class="justify-start">
                <v-btn
                    color="primary"
                    @click="submit"
                >Submit
                </v-btn>
              </v-card-actions>
            </v-card>
          </template>
        </v-dialog>

        <!-- DELETE BUTTON-->

        <v-btn
            color="error"
            plain
            @click="deleteFeedback"
        >
          Delete
        </v-btn>

        <v-snackbar
            v-model="remove.snackbar"
            :timeout="remove.timeout"
        >
          {{ remove.text }}

          <template v-slot:action="{ attrs }">
            <v-btn
                v-bind="attrs"
                color="blue"
                text
                @click="interruptDeletion"
            >
              Undo
            </v-btn>
          </template>
        </v-snackbar>

      </v-col>
    </v-row>

  </div>

</template>

<script>
import TypeBadge from "../../components/TypeBadge";
import DateService from "../../services/DateService";

export default {
  components: {TypeBadge},
  /**
   * ==============================
   * Local state.
   * ==============================
   */
  data() {
    return {
      currentProject: "",
      error: "",
      feedback: "",
      buckets: {
        loading: false,
        error: "",
        data: [],
        selected: "",
        dialog: false
      },
      remove: {
        snackbar: false,
        interrupted: false,
        timeout: 6000,
        text: "Feedback deleted"
      }

    }
  },
  /**
   * ==============================
   * Methods
   * ==============================
   */
  methods: {
    /**
     * Make a date object look pretty.
     */
    formatDate(date) {
      return DateService.getFormattedDate(date);
    },

    /**
     * Load all buckets of the current project.
     *
     * This is used when the user clicks on "Add to bucket"
     */
    async getBuckets() {
      // First check if we already have the buckets.
      if (Array.isArray(this.buckets.data) && this.buckets.data.length !== 0 && !this.buckets.loading) {
        return;
      }

      // Change the loading state so we can show a spinner.
      this.buckets.loading = true;

      try {
        // Query the api.
        const res = await this.$axios.get(`/api/buckets/${this.currentProject}`);

        // If successful, store resonse in local state.
        if (res.status === 200) {
          this.buckets.data = res.data;
          this.buckets.loading = false;
        }
      } catch (e) {
        this.buckets.loading = false;
        this.buckets.error = "Sorry, something went wrong.";
      }
    },
    /**
     * Add the feedback to a bucket.
     */
    async submit() {

      // Check that the bucket we want to assign the feedback to is a new one.
      if (parseInt(this.buckets.selected) === parseInt(this.feedback.bucket_id) || !this.buckets.selected) {
        //@todo: show error message.
        return;
      }

      try {
        // Make post request to api.
        const res = await this.$axios.post('/api/feedback/addbucket', {
          feedback_id: this.feedback.id,
          bucket_id: this.buckets.selected,
        });

        if (res.status === 200) {
          //@todo: handle success message.
        }
        this.buckets.dialog = false;
      } catch (e) {
        this.buckets.dialog = false;
      }
    },
    /**
     * Delete a feedback.
     *
     * A click on the delete button will trigger the snackbar. If the user does not hit "undo"
     * the feedback will be deleted and the user will be redirected to the home page.
     */
    async deleteFeedback() {
      // Show snackbar.
      this.remove.snackbar = true;
      this.remove.interrupted = false;

      let _this = this;

      // Wait 6 seconds.
      setTimeout(async function () {

        // Delete feedback if user dit not hit "undo".
        if (!_this.remove.interrupted) {
          try {
            // Query the api and redirect the user.
            const res = await _this.$axios.delete(`/api/feedback/${_this.currentProject}/${_this.feedback.id}`);
            if(res.status === 2000) {
              _this.$router.push("/");
            }
          } catch (e) {
           //@todo: display proper error message.
          }
        }
      }, this.remove.timeout)
    },
    interruptDeletion() {
      this.remove.interrupted = true;
      this.remove.snackbar = false;
    }
  },
  /**
   * ==============================
   * Fetch hook.
   * ==============================
   */
  async fetch() {
    // Get the currently selected project from cookies.
    const currentProject = this.$cookies.get('currentProject');
    if (!currentProject) {
      this.error = "Sorry, you must select a project if you want to access any feedback."
      return this.feedback = false;
    }
    this.currentProject = currentProject;

    const feedbackID = this.$route.params.id;

    try {
      // Query api to get data for the feedback.
      const res = await this.$axios.get(`/api/feedback/${currentProject}/${feedbackID}`);

      // Successful response.
      if (res.status === 200) {
        this.error = "";
        this.buckets.selected = res.data.bucket_id || "";
        return this.feedback = res.data;
      }

    } catch (e) {
      this.error = "Sorry, something went wrong. Please try again later."
      return this.feedback = false;
    }
  },
  /**
   * ==============================
   * Fetch hook
   * ==============================
   */
  head(){
    return {
      title: 'Feedback'
    }
  }
}

</script>