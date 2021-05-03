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

  <v-row v-else>
    <v-col cols="12" md="6">
      <form @submit.prevent="submit">
        <h1>Your projects</h1>
        <v-radio-group v-model="selectedProject">
          <v-radio
              v-for="project in projects"
              :key="project.name"
              :label="project.name"
              :value="project.id"
          ></v-radio>
        </v-radio-group>


        <!-- SUBMIT BUTTON -->
        <v-btn
            :loading="busy"
            class="mr-4"
            color="primary"
            type="submit"
        >
          Change current project
        </v-btn>
      </form>
    </v-col>

    <!--Integration Guide-->
    <v-col class="grey--lighten" cols="12" md="6">
      <h1>Integration Guide</h1>
      <p>In order to integrate the Feedbeggar widget on your website, you must insert a small code snippet into your
        HTML file. But DON'T WORRY, it's really simple :). Here are the steps you must follow:</p>

      <v-list-item v-for="step in integrationHelper.steps">
        <v-list-item-content>
          <v-list-item-title>{{ step }}</v-list-item-title>
        </v-list-item-content>
      </v-list-item>

      <div class="py-2 px-2 rounded info" >

        {{integrationHelper.codeSnippetPart1}}{{selectedProject}}{{integrationHelper.codeSnippetPart2}}
      </div>

    </v-col>


  </v-row>

</template>

<script>
export default {

  data() {
    return {
      projects: false,
      busy: false,
      selectedProject: null,
      integrationHelper: {
        steps: [
          '1. Navigate to the `.html` file you want to integrate the widget on.',
          '2. Go to the "Head" Tag, which typically is somewhere at the top of the file.',
          '3. Insert the following string just below the opening Head-Tag:'
        ],
        codeSnippetPart1: "<script src='widget.feedbeggar.com/id=",
        codeSnippetPart2: "' defer><\/script>"
      }
    }
  },
  methods: {
    //Submit button
    submit() {
      if (!this.selectedProject) {
        //@TODO: Show error message.
        return;
      }

      this.busy = true;

      // Set the current project in local storage.
      this.$cookies.set("currentProject", this.selectedProject, {
        path: '/',
        maxAge: 60 * 60 * 24 * 50000
      })

      // Reload the page in order to refresh the stores.
      location.reload();
    },
  },

  /**
   * asynData hook.
   * @returns {Promise<{projects: any}|{projects: boolean}>}
   */
  async fetch() {
    try {
      const user = this.$auth.user;

      if (!user) {
        this.projects = false
      }

      // Get the currently selected project from cookies.
      const currentProject = this.$cookies.get("currentProject");

      if (currentProject) {
        this.selectedProject = currentProject;
      }

      const res = await this.$axios.get("/api/projects");

      if (res.status === 200) {
        return this.projects = res.data;
      }
      this.projects = false
    } catch (e) {
      this.projects = false
    }
  },
};
</script>
