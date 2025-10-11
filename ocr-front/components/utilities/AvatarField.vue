<template>
  <div class="text-center">
    <v-badge
      bordered
      color="primary"
      icon="fal fa-plus"
      overlap
      bottom
      offset-x="40px"
      offset-y="20px"
    >
      <v-img
        id="pick-avatar"
        :src="value ? baseURL + value : '/avatar.png'"
        width="100px"
        height="100px"
        class="mx-auto elevation-5 rounded-circle"
        cover
        content-class="cursor-pointer"
      />
    </v-badge>

    <div class="card-footer text-muted" v-html="message"></div>

    <avatar-cropper
      v-bind="{ ...defaultProps, ...getProp('*', {}) }"
      v-on="getFromField('events', {})"
    />
  </div>
</template>

<script>
import AvatarCropper from "vue-avatar-cropper";
import { AbstractField } from "majra";

export default {
  components: { AvatarCropper },

  extends: AbstractField,

  data() {
    return {
      loading: false,
      message: "",
      defaultProps: {
        "upload-handler": this.cropperHandler,
        trigger: "#pick-avatar",
        labels: { submit: "OK", cancel: "Cancel" },
        "cropper-options": {
          aspectRatio: 0.75,
          autoCropArea: 1,
          viewMode: 1,
          movable: true,
          zoomable: true,
        },
      },
    };
  },

  computed: {
    baseURL() {
      return process.env.baseURL + "api/show-file/";
    },
  },

  methods: {
    cropperHandler(cropper) {
      this._event("loading", true);
      this.loading = true;

      const DataURIToBlob = (dataURI) => {
        const splitDataURI = dataURI.split(",");
        const byteString = splitDataURI[0].includes("base64")
          ? atob(splitDataURI[1])
          : decodeURI(splitDataURI[1]);
        const mimeString = splitDataURI[0].split(":")[1].split(";")[0];

        const ia = new Uint8Array(byteString.length);
        for (let i = 0; i < byteString.length; i++) ia[i] = byteString.charCodeAt(i);

        return new Blob([ia], { type: mimeString });
      };
      const formData = new FormData();
      const file = DataURIToBlob(
        cropper.getCroppedCanvas().toDataURL(this.cropperOutputMime)
      );
      formData.append("file", file);
      setTimeout(() => {
        this.$axios
          .$post(this.field.uploadPath, formData)
          .then((response) => {
            this.updateField(response.link);

            this._event("alert", {
              text: this.$t("Uploaded successfully"),
              color: "green",
            });
          })
          .catch(() => {
            this._event("alert", {
              text: this.$t("There was a problem sending the file"),
              color: "red",
            });
          })
          .finally(() => {
            this._event("loading", false);
            this.loading = false;
          });
      }, 1000);
    },
    handleUploaded(response) {
      if (response.status === "success") {
        this.user.avatar = response.url;
        // Maybe you need call vuex action to
        // update user avatar, for example:
        // this.$dispatch('updateUser', {avatar: response.url})
        this.message = "user avatar updated.";
      }
    },
  },
};
</script>

<style>
.vue-avatar-cropper-demo {
  max-width: 18em;
  margin: 0 auto;
}

.avatar {
  width: 160px;
  border-radius: 6px;
  display: block;
  margin: 20px auto;
}

.card-img-overlay {
  display: none;
  transition: all 0.5s;
}

.card-img-overlay button {
  margin-top: 20vh;
}

.card:hover .card-img-overlay {
  display: block;
}
</style>
