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

  <div v-else-if="!currentProject">
    Please select a project in order to see the corresponding feedback.
    You can do this
    <nuxt-link to="/projects">here</nuxt-link>.
  </div>

  <div v-else>
    <!-- Headline-->
    <v-row>
      <v-col class="d-flex justify-start align-center" cols="12">
        <h1>Inbox</h1>
        <v-icon
            class="ml-2 primary--text"
            @click="csvDownload"
        >mdi-download
        </v-icon>
      </v-col>
    </v-row>

    <!-- Table-->


    <v-row>
      <v-col cols="12">
        <v-data-table
            :headers="headers"
            :items="items"
            :items-per-page="5"
            class="elevation-1"
            @click:row="routeToSingle"
        >
          <template v-slot:item.type="{ item }">
            <type-badge :type="item.type"></type-badge>
          </template>

          <template v-slot:item.created_at="{ item }">
            <span>
            {{ formatDate(new Date(item.created_at)) }}
            </span>
          </template>
        </v-data-table>
      </v-col>
    </v-row>

    <!--    Snackbar when deleting the bucket-->
    <v-snackbar
        v-model="snackbar.show"
        :timeout="snackbar.timeout"
    >
      <span :class="snackbar.error ? 'error--text' : 'green--text'">
      {{ snackbar.text }}
      </span>

      <template v-slot:action="{ attrs }">
        <v-btn
            v-bind="attrs"
            text
            @click="snackbar.show = false"
        >
          "Ok"
        </v-btn>
      </template>
    </v-snackbar>

  </div>
</template>

<script>
import DateService from "../services/DateService";
import DownloadService from "../services/DownloadService";
import TypeBadge from "../components/TypeBadge";

export default {
  /**
   * ==============================
   * Local state.
   * ==============================
   */
  data() {
    return {
      currentProject: "",
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
      items: false,
      itemsError: "",
      snackbar: {
        timeout: 5000,
        show: false,
        error: false,
        text: ""
      }
    }
  },
  /**
   * ==============================
   * Components
   * ==============================
   */
  components: {TypeBadge},
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
     * Download the feedback of the current project as csv.
     */
    async csvDownload() {
      try {
        // Query api for csv data.
        const res = await this.$axios.get(`/api/download/feedback/${this.currentProject}`, {
          responseType: "blob",
        })

        // Download csv file immediately.
        if (res.status === 200) {
          DownloadService.downloadAsCsv(res.data);
        }
      } catch (e) {
        this.snackbar = {
          show: true,
          error: true,
          text: "Sorry, something went wrong."
        }
      }

    }
  },
  /**
   * ==============================
   * Fetch hook
   * ==============================
   */
  async fetch() {
    // Get the currently selected project from cookies.
    const currentProject = this.$cookies.get('currentProject');
    if (!currentProject) {
      this.itemsError = "Sorry, you either do not have any projects yet or you did not select one as your current project."
      return this.items = false;
    }

    this.currentProject = currentProject;

    try {
      const res = await this.$axios.get(`/api/feedback/${currentProject}`);

      if (res.status === 200) {
        return this.items = res.data;
      }

    } catch (e) {
      this.items = false;
      this.itemsError = "Sorry, something went wrong. Please try again later."
    }
  }
};
</script>
