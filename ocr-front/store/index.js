const state = () => ({
  OcrMatch: []
});

const getters = {
  getItemsWithKey: (state) => (key) => {
    return state[key] || [];
  }
};

export const mutations = {
  updateOcrMatchItem(state, updatedItem) {
    const index = state.OcrMatch.findIndex(item => item.id === updatedItem.id);
    if (index !== -1) {
      state.OcrMatch.splice(index, 1, updatedItem);
    }
  },
  setOcrMatchItems(state, items) {
    state.OcrMatch = items;
  }
};

const actions = {
  nuxtServerInit({ commit }, { req, $gates }) {
    const user = req.session.user;
    if (user) {
      $gates.setRoles(user.roles);
      $gates.setPermissions(user.permissions);

      commit("user", user);
    }
  }
};


export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
};
