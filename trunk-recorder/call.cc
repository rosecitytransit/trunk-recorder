#include "call.h"

void Call::create_filename() {
  tm *ltm = localtime(&start_time);

  std::stringstream path_stream;

  path_stream << this->config.capture_dir <<  "/" << 1900 + ltm->tm_year << "/" <<  1 + ltm->tm_mon << "/" << ltm->tm_mday;

  boost::filesystem::create_directories(path_stream.str());
if (source) {
  if ((freq != 770081250) && (freq != 770331250) && (freq != 771206250) && (freq != 771256250) && (freq != 771481250) && (freq != 771681250) && (freq != 771756250) && (freq != 772056250) && (freq != 772356250) && (freq != 772431250) && (freq != 772856250) && (freq != 773231250)) {
      sprintf(filename, "%s/%02d%02d%02d-%ld-%ld-ms.wav",
      path_stream.str().c_str(), ltm->tm_hour, ltm->tm_min, ltm->tm_sec, talkgroup, source);
  } else {
      sprintf(filename, "%s/%02d%02d%02d-%ld-%ld.wav",
      path_stream.str().c_str(), ltm->tm_hour, ltm->tm_min, ltm->tm_sec, talkgroup, source);
  }
} else {
  if ((freq != 770081250) && (freq != 770331250) && (freq != 771206250) && (freq != 771256250) && (freq != 771481250) && (freq != 771681250) && (freq != 771756250) && (freq != 772056250) && (freq != 772356250) && (freq != 772431250) && (freq != 772856250) && (freq != 773231250)) {
    sprintf(filename, "%s/%02d%02d%02d-%ld-ms.wav",						//sprintf(filename,        "%s/%ld-%ld_%g.wav",
    path_stream.str().c_str(), ltm->tm_hour, ltm->tm_min, ltm->tm_sec, talkgroup);	//path_stream.str().c_str(), talkgroup, start_time, freq);
  } else {
    sprintf(filename, "%s/%02d%02d%02d-%ld.wav",						//sprintf(filename,        "%s/%ld-%ld_%g.wav",
    path_stream.str().c_str(), ltm->tm_hour, ltm->tm_min, ltm->tm_sec, talkgroup);	//path_stream.str().c_str(), talkgroup, start_time, freq);
  }
}
  // sprintf(filename, "%s/%ld-%ld.wav",
  // path_stream.str().c_str(),talkgroup,start_time);
  // sprintf(status_filename, "%s/%ld-%ld.json",
  // path_stream.str().c_str(),talkgroup,start_time);
}

Call::Call(long t, double f, Config c) {
  config          = c;
  talkgroup       = t;
  freq            = f;
  start_time      = time(NULL);
  last_update     = time(NULL);
  state           = monitoring;
  debug_recording = false;
  recorder        = NULL;
  tdma            = false;
  encrypted       = false;
  emergency       = false;
source = 0;
  this->create_filename();
}

Call::Call(TrunkMessage message, Config c) {
  config          = c;
  talkgroup       = message.talkgroup;
  freq            = message.freq;
  start_time      = time(NULL);
  last_update     = time(NULL);
  state           = monitoring;
  debug_recording = false;
  recorder        = NULL;
  tdma            = message.tdma;
  encrypted       = message.encrypted;
  emergency       = message.emergency;
  source = message.source;
  this->create_filename();
}

Call::~Call() {
  //  BOOST_LOG_TRIVIAL(info) << " This call is over!!";
}

void Call::stop_call() {
  if (state == recording) {
    state         = stopping;
    stopping_time = time(NULL);
    this->get_recorder()->stop();
  }  else {
    BOOST_LOG_TRIVIAL(error) << "\tStopping stopping Call \tTG: " << this->get_talkgroup() << "\tElapsed: " << this->elapsed();
  }
}

void Call::close_call() {
  char shell_command[200]; std::stringstream sourcestring; std::stringstream intstring;

  if (state == recording) {
    BOOST_LOG_TRIVIAL(error) << "Closing a recording call";
  }
  if ((state == stopping) || (state == recording)) {
    BOOST_LOG_TRIVIAL(info) << "Removing Recorded Call \tTG: " <<   this->get_talkgroup() << "\tLast Update: " << this->since_last_update() << " Call Elapsed: " << this->elapsed() << " Stopping Elapsed: " << this->stopping_elapsed();
    Call_Source *wav_src_list = get_recorder()->get_source_list();
    int wav_src_count = get_recorder()->get_source_count();
    for (int i = 0; i < wav_src_count; i++) {
        intstring.str("-"); intstring << wav_src_list[i].source << "-";
        if ((sourcestring.str().append("-").find(intstring.str())==std::string::npos) && (wav_src_list[i].source < 50000) && (i < 10))
        { sourcestring << "-" << wav_src_list[i].source; }
    }
    //+ this->emergency
    sprintf(shell_command, "./encode-upload.sh %s %s &", this->get_filename(), sourcestring.str().c_str());  

    this->get_recorder()->close();

    int rc = system(shell_command);
  }

  if (this->get_debug_recording() == true) {
    this->get_debug_recorder()->stop();
  }
}

bool Call::has_stopped() {
  if (state == stopping) {
    if (recorder) {
      bool result = recorder->has_stopped();
      recorder->clear_total_produced();
      return result;
    } else {
      BOOST_LOG_TRIVIAL(error) << "checking has_stopped on a non-recording call";
      return true;
    }
  } else {
    BOOST_LOG_TRIVIAL(error) << "Checking has_stopped on non-stopping call";
    return true;
  }
}

void Call::set_debug_recorder(Recorder *r) {
  debug_recorder = r;
}

Recorder * Call::get_debug_recorder() {
  return debug_recorder;
}

void Call::set_recorder(Recorder *r) {
  recorder = r;
}

Recorder * Call::get_recorder() {
  return recorder;
}

double Call::get_freq() {
  return freq;
}

void Call::set_freq(double f) {
  freq = f;
}

long Call::get_talkgroup() {
  return talkgroup;
}

Call_Source * Call::get_source_list() {
  return get_recorder()->get_source_list();
}

long Call::get_source_count() {
  return get_recorder()->get_source_count();
}

void Call::set_debug_recording(bool m) {
  debug_recording = m;
}

bool Call::get_debug_recording() {
  return debug_recording;
}

void Call::set_state(State s) {
  state = s;
}

State Call::get_state() {
  return state;
}

void Call::set_encrypted(bool m) {
  encrypted = m;
}

bool Call::get_encrypted() {
  return encrypted;
}

void Call::set_emergency(bool m) {
  emergency = m;
}

bool Call::get_emergency() {
  return emergency;
}

void Call::set_tdma(int m) {
  tdma = m;
}

int Call::get_tdma() {
  return tdma;
}

void Call::update(TrunkMessage message) {
  last_update = time(NULL);
}

int Call::since_last_update() {
  return time(NULL) - last_update;
}

long Call::stopping_elapsed() {
  return time(NULL) - stopping_time;
}

long Call::elapsed() {
  return time(NULL) - start_time;
}

long Call::get_start_time() {
  return start_time;
}

char * Call::get_filename() {
  return filename;
}
