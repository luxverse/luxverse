#include <jni.h>
#include <android/log.h>
#include <string>
#include <vector>
#include "llama.h"

#define TAG "LuxLlm"
#define LOGI(...) __android_log_print(ANDROID_LOG_INFO, TAG, __VA_ARGS__)
#define LOGE(...) __android_log_print(ANDROID_LOG_ERROR, TAG, __VA_ARGS__)

struct LuxCtx {
    llama_model   *model = nullptr;
    llama_context *ctx   = nullptr;
    const llama_vocab *vocab = nullptr;
    llama_sampler *sampler = nullptr;
};

static std::string jstr(JNIEnv *env, jstring s) {
    if (!s) return "";
    const char *c = env->GetStringUTFChars(s, nullptr);
    std::string out(c ? c : "");
    if (c) env->ReleaseStringUTFChars(s, c);
    return out;
}

extern "C" JNIEXPORT jlong JNICALL
Java_org_luxverse_app_ai_LocalLlm_nativeInit(JNIEnv *env, jobject, jstring jpath, jint threads, jint nctx) {
    std::string path = jstr(env, jpath);
    llama_backend_init();

    llama_model_params mp = llama_model_default_params();
    mp.n_gpu_layers = 0;
    llama_model *model = llama_model_load_from_file(path.c_str(), mp);
    if (!model) { LOGE("model load failed: %s", path.c_str()); return 0; }

    llama_context_params cp = llama_context_default_params();
    cp.n_ctx = (uint32_t)(nctx > 0 ? nctx : 2048);
    cp.n_batch = 512;
    cp.n_threads = (int32_t)(threads > 0 ? threads : 4);
    cp.n_threads_batch = cp.n_threads;
    llama_context *ctx = llama_init_from_model(model, cp);
    if (!ctx) { llama_model_free(model); LOGE("context init failed"); return 0; }

    auto *lx = new LuxCtx();
    lx->model = model;
    lx->ctx = ctx;
    lx->vocab = llama_model_get_vocab(model);
    lx->sampler = llama_sampler_chain_init(llama_sampler_chain_default_params());
    llama_sampler_chain_add(lx->sampler, llama_sampler_init_greedy());
    LOGI("model loaded, ctx=%d threads=%d", cp.n_ctx, cp.n_threads);
    return (jlong)(uintptr_t)lx;
}

extern "C" JNIEXPORT jstring JNICALL
Java_org_luxverse_app_ai_LocalLlm_nativeGenerate(JNIEnv *env, jobject, jlong handle, jstring jprompt, jint maxTokens) {
    auto *lx = reinterpret_cast<LuxCtx *>(handle);
    if (!lx || !lx->ctx) return env->NewStringUTF("");
    std::string prompt = jstr(env, jprompt);

    std::vector<llama_token> tokens(prompt.size() + 8);
    int n = llama_tokenize(lx->vocab, prompt.c_str(), (int32_t)prompt.size(),
                           tokens.data(), (int32_t)tokens.size(), true, true);
    if (n < 0) {
        tokens.resize(-n);
        n = llama_tokenize(lx->vocab, prompt.c_str(), (int32_t)prompt.size(),
                           tokens.data(), (int32_t)tokens.size(), true, true);
    }
    if (n <= 0) return env->NewStringUTF("");
    tokens.resize(n);

    llama_batch batch = llama_batch_get_one(tokens.data(), n);
    if (llama_decode(lx->ctx, batch) != 0) { LOGE("decode prompt failed"); return env->NewStringUTF(""); }

    std::string out;
    char piece[256];
    for (int i = 0; i < maxTokens; i++) {
        llama_token id = llama_sampler_sample(lx->sampler, lx->ctx, -1);
        if (llama_vocab_is_eog(lx->vocab, id)) break;
        llama_sampler_accept(lx->sampler, id);
        int len = llama_token_to_piece(lx->vocab, id, piece, sizeof(piece), 0, true);
        if (len > 0) out.append(piece, len);
        batch = llama_batch_get_one(&id, 1);
        if (llama_decode(lx->ctx, batch) != 0) break;
    }
    return env->NewStringUTF(out.c_str());
}

extern "C" JNIEXPORT void JNICALL
Java_org_luxverse_app_ai_LocalLlm_nativeFree(JNIEnv *, jobject, jlong handle) {
    auto *lx = reinterpret_cast<LuxCtx *>(handle);
    if (!lx) return;
    if (lx->sampler) llama_sampler_free(lx->sampler);
    if (lx->ctx) llama_free(lx->ctx);
    if (lx->model) llama_model_free(lx->model);
    delete lx;
    llama_backend_free();
}
