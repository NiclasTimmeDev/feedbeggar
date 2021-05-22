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
      <v-col cols="12" md="6">
        <form @submit.prevent="submit">

          <!--==============================-->
          <!-- Headline.-->
          <!--==============================-->
          <h1>Your projects</h1>

          <!--==============================-->
          <!-- Dialog for adding a new project.-->
          <!--==============================-->
          <template>
            <v-dialog
                v-model="addProject.dialog"
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
                      Add project
                    </v-btn>
                  </v-col>
                </v-row>

              </template>
              <v-card>
                <form @submit.prevent="submitNewProject">
                  <v-card-title>
                    <span class="headline">New Project</span>
                  </v-card-title>
                  <v-card-text>
                    <v-row>
                      <!--Name-->
                      <v-col
                          cols="12"
                      >
                        <!-- Name field.-->
                        <v-text-field
                            v-model="addProject.data.name"
                            :error-messages="nameErrors"
                            hint="The name of your project."
                            label="Name*"
                            required
                            @blur="$v.addProject.data.name.$touch()"
                        ></v-text-field>

                      </v-col>

                      <!--Url (host)-->
                      <v-col
                          cols="12"
                      >
                        <v-text-field
                            v-model="addProject.data.url"
                            :error-messages="urlErrors"
                            hint="Limits the ability to submit feedback to the given URL. Increases security."
                            label="Domain (host)"
                            @blur="$v.addProject.data.name.$touch()"
                        ></v-text-field>
                      </v-col>

                      <!-- Checkbox field for selecting as current project.-->
                      <v-checkbox
                          v-model="addProject.data.setAsCurrentProject"
                          label="Set as current project"
                      ></v-checkbox>

                    </v-row>

                    <small>*indicates required field</small>
                  </v-card-text>
                  <v-card-actions>
                    <v-btn
                        :loading="addProject.loading"
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

          <!--==============================-->
          <!-- List of projects.-->
          <!--==============================-->
          <template>
            <v-dialog
                v-model="updateProject.dialog"
                max-width="600px"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-row>
                  <v-col v-for="(project, i) in projects" :key="project.id" cols="12">
                    <v-btn
                        v-bind="attrs"
                        v-on="on"
                        text
                        @click="setCurrentlyUpdatingProject(project, i)"
                    >
                      {{ project.name }}
                    </v-btn>
                    <v-chip v-if="parseInt(project.id) === parseInt(selectedProject)" outlined>Current project</v-chip>
                  </v-col>
                </v-row>

              </template>
              <v-card>
                <form @submit.prevent="submitUpdatedProject">
                  <v-card-title>
                    <span class="headline">Update Project</span>
                  </v-card-title>
                  <v-card-text>
                    <v-row>
                      <!--Name-->
                      <v-col
                          cols="12"
                      >
                        <!-- Name field.-->
                        <v-text-field
                            v-model="updateProject.data.name"
                            :error-messages="nameUpdateErrors"
                            hint="The name of your project."
                            label="Name*"
                            @blur="$v.updateProject.data.name.$touch()"
                        ></v-text-field>

                      </v-col>

                      <!--Url (host)-->
                      <v-col
                          cols="12"
                      >
                        <v-text-field
                            v-model="updateProject.data.url"
                            :error-messages="urlUpdateErrors"
                            hint="Limits the ability to submit feedback to the given URL. Increases security."
                            label="Domain (host)"
                            @blur="$v.addProject.data.url.$touch()"
                        ></v-text-field>
                      </v-col>

                      <!-- Checkbox field for selecting as current project.-->
                      <v-checkbox
                          v-model="updateProject.data.isCurrentProject"
                          label="Set as current project"
                      ></v-checkbox>

                    </v-row>

                    <small>*indicates required field</small>
                  </v-card-text>
                  <v-card-actions>
                    <v-btn
                        :loading="updateProject.loading"
                        color="primary"
                        dark
                        type="submit"
                    >
                      Update
                    </v-btn>

                    <v-btn
                        :loading="deleteProject.loading"
                        color="error"
                        text
                        @click="submitProjectDeletion"
                    >
                      Delete
                    </v-btn>
                  </v-card-actions>
                </form>
              </v-card>
            </v-dialog>
          </template>

        </form>
      </v-col>

      <!--==============================-->
      <!-- Snackbar for new projects.-->
      <!--==============================-->
      <v-snackbar
          v-model="addProject.snackbar.show"
          :timeout="addProject.snackbar.timeout"
      >
      <span :class="addProject.snackbar.error ? 'error--text' : 'green--text'">
       {{ addProject.snackbar.text }}
      </span>
        <template v-slot:action="{ attrs }">
          <v-btn
              v-bind="attrs"
              color="white"
              text
              @click="addProject.snackbar.show = false"
          >
            Got it
          </v-btn>
        </template>
      </v-snackbar>

      <!--==============================-->
      <!-- Snackbar for new projects.-->
      <!--==============================-->
      <v-snackbar
          v-model="updateProject.snackbar.show"
          :timeout="updateProject.snackbar.timeout"
      >
      <span :class="updateProject.snackbar.error ? 'error--text' : 'green--text'">
       {{ updateProject.snackbar.text }}
      </span>
        <template v-slot:action="{ attrs }">
          <v-btn
              v-bind="attrs"
              color="white"
              text
              @click="updateProject.snackbar.show = false"
          >
            Got it
          </v-btn>
        </template>
      </v-snackbar>

      <!--==============================-->
      <!--Integration Guide.-->
      <!--==============================-->
      <v-col class="grey--lighten" cols="12" md="6">
        <h1>Integration Guide</h1>
        <p>In order to integrate the Feedbeggar widget on your website, you must insert a small code snippet into your
          HTML file. But DON'T WORRY, it's really simple :). Here are the steps you must follow:</p>

        <v-list-item v-for="step in integrationHelper.steps">
          <v-list-item-content>
            <v-list-item-title>{{ step }}</v-list-item-title>
          </v-list-item-content>
        </v-list-item>

        <div class="py-2 px-2 rounded info">

          {{ integrationHelper.codeSnippetPart1 }}{{ selectedProject }}{{ integrationHelper.codeSnippetPart2 }}
        </div>
      </v-col>

    </v-row>
  </div>
</template>

<script>
import {validationMixin} from "vuelidate";
import {required, url} from "vuelidate/lib/validators";

export default {
  /**
   * ==============================
   * Local state
   * ==============================
   */
  data() {
    return {
      projects: false,
      busy: false,
      selectedProject: null,
      addProject: {
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
          url: "",
          setAsCurrentProject: true
        }
      },
      updateProject: {
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
          id: "",
          name: "",
          url: "",
          isCurrentProject: true
        },
        currentlyUpdatingIndex: ""
      },
      deleteProject: {
        loading: false,
        error: false,
        snackbar: {
          timeout: 6000,
          show: false,
          error: false,
          text: ""
        },
        data: {
          id: ""
        }
      },
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
  /**
   * ==============================
   * Validation for new bucket.
   * ==============================
   */
  mixins: [validationMixin],
  validations: {
    addProject: {
      data: {
        name: {required},
        url: {url}
      }
    },
    updateProject: {
      data: {
        name: {required},
        url: {url}
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
      if (!this.$v.addProject.data.name.$dirty) return errors;
      !this.$v.addProject.data.name.required && errors.push("Name is required");
      return errors;
    },
    urlErrors() {
      const errors = [];
      if (!this.$v.addProject.data.url.$dirty) return errors;
      !this.$v.addProject.data.url.url && errors.push("Please enter a valid url.");
      return errors;
    },
    nameUpdateErrors() {
      const errors = [];
      if (!this.$v.updateProject.data.name.$dirty) return errors;
      !this.$v.updateProject.data.name.required && errors.push("Name is required");
      return errors;
    },
    urlUpdateErrors() {
      const errors = [];
      if (!this.$v.updateProject.data.url.$dirty) return errors;
      !this.$v.updateProject.data.url.url && errors.push("Please enter a valid url.");
      return errors;
    },
  },
  /**
   * ==============================
   * Methods
   * ==============================
   */
  methods: {
    /**
     * Change the currently active project.
     */
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
    /**
     * Submit a new project.
     */
    async submitNewProject() {
      // Validate the input data.
      this.$v.$touch();
      if (this.$v.addProject.$invalid) {
        return;
      }

      // Show loading spinner.
      this.addProject.loading = true;

      try {
        // Query the api.
        const res = await this.$axios.post('/api/projects', {
          name: this.addProject.data.name,
          url: this.addProject.data.url || ''
        });

        // Behaviour on success.
        if (res.status === 201) {
          // Show snackbar with success message.
          this.addProject.snackbar = {
            ...this.addProject.snackbar,
            show: true,
            error: false,
            text: "Project created successfully."
          };

          // Add the new project to the data.
          this.projects.push(res.data);

          // If the checkbox is selected, make the new project the current one.
          if (this.addProject.data.setAsCurrentProject) {
            setTimeout(() => {
              this.selectedProject = res.data.id;
              this.submit();
            }, 2000)
          }

        }

        // Hide spinner and close dialog.
        this.addProject.loading = false;
        this.addProject.dialog = false;
      } catch (e) {

        this.addProject.loading = false;
        this.addProject.dialog = false;

        // Show snackbar with error message.
        this.addProject.snackbar = {
          ...this.addProject.snackbar,
          show: true,
          error: true,
          text: "Something went wrong. Please try again later."
        };
      }
    },
    /**
     * Set the state for the currently updating project.
     */
    setCurrentlyUpdatingProject(project, i) {
      this.updateProject.data = {
        ...this.updateProject.data,
        name: project.name,
        url: project.url,
        id: project.id,
        isCurrentProject: parseInt(this.selectedProject) === parseInt(project.id)
      }
      this.updateProject.currentlyUpdatingIndex = i;
    },
    /**
     * Submit an updated project to the api.
     */
    async submitUpdatedProject() {
      // Validate the input data.
      this.$v.$touch();
      if (this.$v.updateProject.$invalid) {
        return;
      }

      // Show loading spinner.
      this.updateProject.loading = true;

      try {
        const res = await this.$axios.patch('/api/projects', {
          project_id: this.updateProject.data.id,
          updates: {
            name: this.updateProject.data.name,
            url: this.updateProject.data.url,
          }
        });

        if (res.status === 200) {
          // Show snackbar with success message.
          this.updateProject.snackbar = {
            ...this.updateProject.snackbar,
            show: true,
            error: false,
            text: "Project updated successfully."
          };

          this.projects[this.updateProject.currentlyUpdatingIndex] = res.data;

          // If the checkbox is selected, make the new project the current one.
          if (this.updateProject.data.isCurrentProject) {
            setTimeout(() => {
              this.selectedProject = this.updateProject.data.id;
              this.submit();
            }, 2000)
          }
        }

      } catch (e) {
        this.updateProject.loading = false;
        this.updateProject.dialog = false;

        // Show snackbar with error message.
        this.updateProject.snackbar = {
          ...this.updateProject.snackbar,
          show: true,
          error: true,
          text: "Something went wrong. Please try again later."
        };
      }

      this.updateProject.loading = false;
      this.updateProject.dialog = false;
    },
    /**
     * Delete a project.
     */
    async submitProjectDeletion(){
      if(!this.updateProject.data.id) {
        //@TODO: Show proper error message.
        return;
      }

      try {
        const res = await this.$axios.delete(`/api/projects/${this.updateProject.data.id}`);

        console.log(res);
      } catch(e) {
        console.log(e);
      }


    }
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
  /**
   * ==============================
   * Fetch hook
   * ==============================
   */
  head(){
    return {
      title: 'Projects'
    }
  }
};
</script>
