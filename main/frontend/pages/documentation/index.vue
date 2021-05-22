<template>
  <div>
    <!--Headline-->
    <v-row>
      <v-col cols="12">
        <h1>Documentation!</h1>
      </v-col>
    </v-row>

    <!--Integration guide-->
    <template>
      <v-expansion-panels>
        <v-expansion-panel
            v-for="(item,i) in panels"
            :key="i"
        >
          <v-expansion-panel-header>
            <h2>
              {{ item.title }}
            </h2>
          </v-expansion-panel-header>
          <v-expansion-panel-content>
            <div v-html="item.text"></div>
          </v-expansion-panel-content>
        </v-expansion-panel>
      </v-expansion-panels>
    </template>
  </div>
</template>

<script>
export default {
  data() {
    return {
      panels: [
        {
          title: "Projects",
          text: "<h3>What are projects</h3><p>As you might have different projects you might want to collect feedback for those separately. For example, the feedback on your block is completely independent from you SaaS business. By creating different projects you do not have to create separate Feedbeggar accounts in order to collect feedback to your businesses separately,</p><h3>Why must I set a current project</h3>\n" +
              "<p>You might have realized that many pages do not show any valuable information if you do not have a current project selected. This is because if you don't, we can't know for which of your projects you want your feedback and buckets to be displayed. Thus, be sure to always have one selected and double check your current project.</p>"

        },
        {
          title: "Integration Guide",
          text: "For a detailed guide on how to integrate the widget to your website visit the <nuxt-link to='/projects' >Projects</nuxt-link> Page."
        },
        {
          title: "Buckets",
          text: "<h3>What is a bucket?</h3><p>Buckets are a way for you to organize your feedback. Sometimes you receive feedback from different people that request the same feature or have the same idea but say it in different words. Buckets are a way for you to group those together so you have a better overview about what is requested often or not.</p><h3>How many buckets can I create?</h3>\n" +
              "<p>You can create as many buckets as you want. However, you can assign one feedback to one bucket only.</p>"
        }
      ]
    }
  },
  /**
   * ==============================
   * Fetch hook
   * ==============================
   */
  head(){
    return {
      title: 'Docs'
    }
  }
}
</script>