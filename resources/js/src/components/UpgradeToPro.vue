<template>

</template>

<script>
export default {}
</script>

<style lang="scss" scoped>
.product-upgrade-to-pro {
  z-index: 4;
  bottom: 5%;
  right: 48px;
  position: fixed;
  .v-application &.v-btn.v-btn--is-elevated {
    box-shadow: 0 1px 20px 1px #ff4c51 !important;
    &:hover {
      box-shadow: none !important;
    }
  }
}
</style>
